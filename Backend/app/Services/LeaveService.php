<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    protected const RELATIONS = ['employee:id,first_name,middle_name,last_name,employee_number', 'leaveType:id,name,is_paid,requires_approval', 'approver:id,name'];

    public function __construct(protected LeaveBalanceService $balanceService) {}

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Leave::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('start_date');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): Leave
    {
        return Leave::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    public function create(array $data): Leave
    {
        $businessId = Auth::user()->business_id;
        $employee = EmployeeProfile::where('id', $data['employee_id'])->where('business_id', $businessId)->firstOrFail();
        $leaveType = LeaveType::where('id', $data['leave_type_id'])->where('business_id', $businessId)->firstOrFail();

        $workingDays = $data['working_days'] ?? $this->calculateWorkingDays($data['start_date'], $data['end_date']);
        $calendarDays = $this->calculateCalendarDays($data['start_date'], $data['end_date']);
        $status = $data['status'] ?? 'submitted';

        $this->guardEligibility($employee, $leaveType);
        $this->guardOverlap($employee->id, $data['start_date'], $data['end_date']);
        if ($status !== 'draft') {
            $this->guardBalance($employee->id, $leaveType, $data['start_date'], $workingDays);
        }

        return DB::transaction(function () use ($data, $employee, $leaveType, $calendarDays, $workingDays, $status) {
            $leave = Leave::create([
                'business_id' => $employee->business_id,
                'user_id' => $employee->user_id,
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days' => $calendarDays,
                'working_days' => $workingDays,
                'reason' => $data['reason'] ?? null,
                'status' => $status,
            ]);

            if (!empty($data['supporting_documents'])) {
                $this->attachDocuments($leave, $data['supporting_documents']);
            }

            if ($status !== 'draft' && $leaveType->is_paid) {
                $this->balanceService->reservePending($employee->id, $leaveType->id, $this->yearOf($data['start_date']), $workingDays);
            }

            return $leave->fresh(self::RELATIONS);
        });
    }

    /**
     * Editing is only allowed while a request hasn't been decided yet
     * (draft/submitted) — once approved/rejected/completed/cancelled the
     * only way to change it is a fresh request. Any pending hold on the
     * old dates/days is released and re-guarded/re-reserved against the
     * new ones, so an edit can't silently bypass overlap/balance checks.
     */
    public function update(int $id, array $data): Leave
    {
        return DB::transaction(function () use ($id, $data) {
            $leave = $this->find($id);

            if (!in_array($leave->status, ['draft', 'submitted'])) {
                throw ValidationException::withMessages([
                    'status' => ['Only draft or submitted leave requests can be edited.'],
                ]);
            }

            $businessId = Auth::user()->business_id;
            $leaveType = isset($data['leave_type_id'])
                ? LeaveType::where('id', $data['leave_type_id'])->where('business_id', $businessId)->firstOrFail()
                : $leave->leaveType;

            $startDate = $data['start_date'] ?? $leave->start_date->toDateString();
            $endDate = $data['end_date'] ?? $leave->end_date->toDateString();
            $workingDays = $data['working_days'] ?? $this->calculateWorkingDays($startDate, $endDate);
            $calendarDays = $this->calculateCalendarDays($startDate, $endDate);

            $employee = $leave->employee;
            $oldYear = $this->yearOf($leave->start_date->toDateString());
            $oldDays = (float) ($leave->working_days ?? $leave->days);
            $wasHeld = $leave->status === 'submitted' && $leave->leaveType->is_paid;

            if ($wasHeld) {
                $this->balanceService->releasePending($leave->employee_id, $leave->leave_type_id, $oldYear, $oldDays);
            }

            $this->guardEligibility($employee, $leaveType);
            $this->guardOverlap($employee->id, $startDate, $endDate, $leave->id);
            if ($leave->status === 'submitted') {
                $this->guardBalance($employee->id, $leaveType, $startDate, $workingDays);
            }

            $leave->update([
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $calendarDays,
                'working_days' => $workingDays,
                'reason' => $data['reason'] ?? $leave->reason,
            ]);

            if (!empty($data['supporting_documents'])) {
                $this->attachDocuments($leave, $data['supporting_documents']);
            }

            if ($leave->status === 'submitted' && $leaveType->is_paid) {
                $this->balanceService->reservePending($employee->id, $leaveType->id, $this->yearOf($startDate), $workingDays);
            }

            return $leave->fresh(self::RELATIONS);
        });
    }

    /**
     * draft -> submitted reserves the balance; submitted -> approved moves
     * the reservation to used; submitted/approved -> rejected/cancelled
     * releases whatever was held. Every other transition (e.g. approved ->
     * completed) is a status label change only, no balance effect.
     */
    public function updateStatus(int $id, string $status, ?string $notes = null): Leave
    {
        return DB::transaction(function () use ($id, $status, $notes) {
            $leave = $this->find($id);
            $from = $leave->status;
            $leaveType = $leave->leaveType;
            $year = $this->yearOf($leave->start_date->toDateString());
            $days = (float) ($leave->working_days ?? $leave->days);

            if ($leaveType->is_paid && $leave->employee_id) {
                if ($from === 'draft' && $status === 'submitted') {
                    $this->balanceService->reservePending($leave->employee_id, $leaveType->id, $year, $days);
                } elseif (in_array($from, ['draft', 'submitted']) && $status === 'approved') {
                    if ($from === 'draft') {
                        $this->balanceService->reservePending($leave->employee_id, $leaveType->id, $year, $days);
                    }
                    $this->balanceService->confirmUsed($leave->employee_id, $leaveType->id, $year, $days);
                } elseif ($from === 'submitted' && in_array($status, ['rejected', 'cancelled'])) {
                    $this->balanceService->releasePending($leave->employee_id, $leaveType->id, $year, $days);
                } elseif ($from === 'approved' && $status === 'cancelled') {
                    $this->balanceService->releaseUsed($leave->employee_id, $leaveType->id, $year, $days);
                }
            }

            $payload = ['status' => $status];
            if (in_array($status, ['approved', 'rejected'])) {
                $payload['approved_by'] = Auth::id();
                $payload['approved_at'] = now();
            }
            if ($notes !== null) {
                $payload['notes'] = $notes;
            }

            $leave->update($payload);

            return $leave->fresh(self::RELATIONS);
        });
    }

    public function delete(int $id): void
    {
        $leave = $this->find($id);

        // Deleting a still-open request shouldn't leave a phantom hold on
        // the balance behind it.
        if ($leave->leaveType?->is_paid && $leave->employee_id) {
            $days = (float) ($leave->working_days ?? $leave->days);
            $year = $this->yearOf($leave->start_date->toDateString());
            if ($leave->status === 'submitted') {
                $this->balanceService->releasePending($leave->employee_id, $leave->leave_type_id, $year, $days);
            } elseif ($leave->status === 'approved') {
                $this->balanceService->releaseUsed($leave->employee_id, $leave->leave_type_id, $year, $days);
            }
        }

        $leave->delete();
    }

    public function documentPath(int $id, string $path): string
    {
        $leave = $this->find($id);
        $exists = collect($leave->supporting_documents ?? [])->contains(fn ($doc) => $doc['path'] === $path);
        abort_unless($exists, 404, 'Document not found on this leave request.');
        return $path;
    }

    protected function attachDocuments(Leave $leave, array $files): void
    {
        $documents = collect($leave->supporting_documents ?? []);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $path = $file->store("leave-documents/{$leave->id}", 'local');
            $documents->push([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'uploaded_at' => now()->toDateTimeString(),
            ]);
        }

        $leave->update(['supporting_documents' => $documents->values()->all()]);
    }

    protected function guardEligibility(EmployeeProfile $employee, LeaveType $leaveType): void
    {
        if ($leaveType->gender_eligibility && $employee->gender && strtolower($leaveType->gender_eligibility) !== strtolower($employee->gender)) {
            throw ValidationException::withMessages([
                'leave_type_id' => ["{$leaveType->name} is only available to {$leaveType->gender_eligibility} employees."],
            ]);
        }
    }

    protected function guardOverlap(int $employeeId, string $startDate, string $endDate, ?int $excludeLeaveId = null): void
    {
        $overlaps = Leave::where('employee_id', $employeeId)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->when($excludeLeaveId, fn ($q) => $q->where('id', '!=', $excludeLeaveId))
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'start_date' => ['This employee already has a leave request that overlaps these dates.'],
            ]);
        }
    }

    protected function guardBalance(int $employeeId, LeaveType $leaveType, string $startDate, float $workingDays): void
    {
        if (!$leaveType->is_paid) {
            return;
        }

        $available = $this->balanceService->available($employeeId, $leaveType->id, $this->yearOf($startDate));

        if ($workingDays > $available) {
            throw ValidationException::withMessages([
                'working_days' => ["Only {$available} day(s) of {$leaveType->name} are available — this request needs {$workingDays}."],
            ]);
        }
    }

    protected function calculateCalendarDays(string $start, string $end): int
    {
        return (int) now()->parse($start)->diffInDays(now()->parse($end)) + 1;
    }

    protected function calculateWorkingDays(string $start, string $end): int
    {
        $period = now()->parse($start);
        $endDate = now()->parse($end);
        $count = 0;

        while ($period->lte($endDate)) {
            if (!$period->isWeekend()) {
                $count++;
            }
            $period->addDay();
        }

        return max($count, 1);
    }

    protected function yearOf(string $date): int
    {
        return (int) now()->parse($date)->format('Y');
    }
}
