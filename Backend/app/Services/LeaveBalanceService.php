<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    protected const RELATIONS = ['employee:id,first_name,middle_name,last_name,employee_number', 'leaveType:id,name,is_paid'];

    public function list(array $filters = []): Collection
    {
        $query = LeaveBalance::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderBy('year', 'desc');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }
        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        return $query->get();
    }

    public function find(int $id): LeaveBalance
    {
        return LeaveBalance::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    /**
     * Every employee gets a balance row lazily, the first time one is
     * needed (viewing it, or submitting a leave request against that
     * type/year) — no batch job has to pre-create a row for every
     * employee x leave-type x year combination up front.
     */
    public function getOrCreate(int $employeeId, int $leaveTypeId, int $year): LeaveBalance
    {
        $businessId = Auth::user()->business_id;

        $existing = LeaveBalance::where('business_id', $businessId)
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if ($existing) {
            return $existing;
        }

        $leaveType = LeaveType::where('id', $leaveTypeId)->where('business_id', $businessId)->first();

        return LeaveBalance::create([
            'business_id' => $businessId,
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'year' => $year,
            'opening_balance' => $leaveType->days_allowed ?? 0,
        ]);
    }

    /**
     * Mandatory-reason manual correction — the one path where a balance
     * changes outside the automatic pending/used bookkeeping that leave
     * approval/rejection does. Kept in its own ledger table so every
     * correction is individually attributable, not folded silently into
     * opening_balance.
     */
    public function adjust(int $id, float $amount, string $reason): LeaveBalance
    {
        return DB::transaction(function () use ($id, $amount, $reason) {
            $balance = $this->find($id);

            $balance->adjustmentHistory()->create([
                'amount' => $amount,
                'reason' => $reason,
                'adjusted_by' => Auth::id(),
            ]);

            $balance->update(['adjustments' => (float) $balance->adjustments + $amount]);

            return $balance->fresh(self::RELATIONS);
        });
    }

    public function adjustmentHistory(int $id): Collection
    {
        return $this->find($id)->adjustmentHistory()->with('adjustedBy:id,name')->latest()->get();
    }

    /**
     * How many days of this leave type the employee can still take —
     * closing balance, but ignoring is_paid types the caller has already
     * decided not to track (see LeaveService).
     */
    public function available(int $employeeId, int $leaveTypeId, int $year): float
    {
        return $this->getOrCreate($employeeId, $leaveTypeId, $year)->closing_balance;
    }

    public function reservePending(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $balance = $this->getOrCreate($employeeId, $leaveTypeId, $year);
        $balance->update(['pending_days' => (float) $balance->pending_days + $days]);
    }

    public function releasePending(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $balance = $this->getOrCreate($employeeId, $leaveTypeId, $year);
        $balance->update(['pending_days' => max(0, (float) $balance->pending_days - $days)]);
    }

    public function confirmUsed(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $balance = $this->getOrCreate($employeeId, $leaveTypeId, $year);
        $balance->update([
            'pending_days' => max(0, (float) $balance->pending_days - $days),
            'used_days' => (float) $balance->used_days + $days,
        ]);
    }

    public function releaseUsed(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $balance = $this->getOrCreate($employeeId, $leaveTypeId, $year);
        $balance->update(['used_days' => max(0, (float) $balance->used_days - $days)]);
    }

    /**
     * Materializes a balance row for every active employee x active paid
     * leave type for the given year, so a fresh business's balances page
     * isn't empty until leave requests start being submitted.
     */
    public function ensureForYear(int $year): Collection
    {
        $businessId = Auth::user()->business_id;

        $employeeIds = EmployeeProfile::where('business_id', $businessId)
            ->where('employment_status', 'active')
            ->pluck('id');

        $leaveTypeIds = LeaveType::where('business_id', $businessId)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($employeeIds as $employeeId) {
            foreach ($leaveTypeIds as $leaveTypeId) {
                $this->getOrCreate($employeeId, $leaveTypeId, $year);
            }
        }

        return $this->list(['year' => $year]);
    }
}
