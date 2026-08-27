<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\SalaryAdvance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SalaryAdvanceService
{
    protected const RELATIONS = ['employee:id,first_name,middle_name,last_name,employee_number', 'requester:id,name', 'approver:id,name'];

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = SalaryAdvance::where('business_id', Auth::user()->business_id)
            ->with(array_merge(self::RELATIONS, ['repayments']))
            ->orderByDesc('created_at');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): SalaryAdvance
    {
        return SalaryAdvance::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(array_merge(self::RELATIONS, ['repayments']))
            ->firstOrFail();
    }

    public function create(array $data): SalaryAdvance
    {
        $businessId = Auth::user()->business_id;
        EmployeeProfile::where('id', $data['employee_id'])->where('business_id', $businessId)->firstOrFail();

        $advance = SalaryAdvance::create([
            'business_id' => $businessId,
            'employee_id' => $data['employee_id'],
            'amount' => $data['amount'],
            'repayment_amount_per_period' => $data['repayment_amount_per_period'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
            'requested_by' => Auth::id(),
        ]);

        return $advance->fresh(self::RELATIONS);
    }

    public function approve(int $id): SalaryAdvance
    {
        $advance = $this->find($id);
        if ($advance->status !== 'pending') {
            throw ValidationException::withMessages(['status' => ['Only a pending advance can be approved.']]);
        }
        $advance->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now(), 'disbursed_at' => now()]);
        return $advance->fresh(self::RELATIONS);
    }

    public function reject(int $id, ?string $notes = null): SalaryAdvance
    {
        $advance = $this->find($id);
        if ($advance->status !== 'pending') {
            throw ValidationException::withMessages(['status' => ['Only a pending advance can be rejected.']]);
        }
        $advance->update(['status' => 'rejected', 'approved_by' => Auth::id(), 'approved_at' => now(), 'notes' => $notes]);
        return $advance->fresh(self::RELATIONS);
    }

    public function cancel(int $id): SalaryAdvance
    {
        $advance = $this->find($id);
        if (!in_array($advance->status, ['pending', 'approved'])) {
            throw ValidationException::withMessages(['status' => ['Only a pending or approved advance can be cancelled.']]);
        }
        $advance->update(['status' => 'cancelled']);
        return $advance->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $advance = $this->find($id);
        if ($advance->status !== 'pending') {
            throw ValidationException::withMessages(['status' => ['Only a pending advance can be deleted.']]);
        }
        $advance->delete();
    }
}
