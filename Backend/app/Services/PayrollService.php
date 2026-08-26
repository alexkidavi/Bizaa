<?php

namespace App\Services;

use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Payroll::where('business_id', Auth::user()->business_id)
            ->with(['user:id,name,email,department_id', 'user.department:id,name'])
            ->orderByDesc('year')
            ->orderByDesc('month');

        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function generate(int $month, int $year): array
    {
        $businessId = Auth::user()->business_id;
        $createdBy  = Auth::id();

        return DB::transaction(function () use ($businessId, $createdBy, $month, $year) {
            $staff = User::where('business_id', $businessId)
                ->whereNotNull('registered_by')
                ->where('is_active', true)
                ->get();

            // Pre-fetched once for the whole batch instead of 2 queries per
            // staff member — for hundreds of staff that was 500-1500+ round
            // trips on a single "Generate Payroll" click.
            $existingUserIds = Payroll::where('business_id', $businessId)
                ->where('month', $month)
                ->where('year', $year)
                ->pluck('user_id')
                ->flip();

            $overtimeByUser = Overtime::whereIn('user_id', $staff->pluck('id'))
                ->where('status', 'approved')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->groupBy('user_id')
                ->map(fn ($rows) => $rows->sum(fn ($ot) => $ot->hours * $ot->rate_per_hour));

            $created = [];
            foreach ($staff as $user) {
                if ($existingUserIds->has($user->id)) {
                    continue;
                }

                $overtimePay = $overtimeByUser->get($user->id, 0);
                $net = $user->basic_salary + $overtimePay;

                $created[] = Payroll::create([
                    'business_id'  => $businessId,
                    'user_id'      => $user->id,
                    'month'        => $month,
                    'year'         => $year,
                    'basic_salary' => $user->basic_salary,
                    'overtime_pay' => $overtimePay,
                    'allowances'   => 0,
                    'deductions'   => 0,
                    'net_salary'   => $net,
                    'status'       => 'draft',
                    'created_by'   => $createdBy,
                ]);
            }

            return $created;
        });
    }

    public function update(int $id, array $data): Payroll
    {
        $payroll = $this->find($id);
        $net = ($data['basic_salary'] ?? $payroll->basic_salary)
             + ($data['overtime_pay'] ?? $payroll->overtime_pay)
             + ($data['allowances'] ?? $payroll->allowances)
             - ($data['deductions'] ?? $payroll->deductions);

        $payroll->update([
            'basic_salary' => $data['basic_salary'] ?? $payroll->basic_salary,
            'overtime_pay' => $data['overtime_pay'] ?? $payroll->overtime_pay,
            'allowances'   => $data['allowances'] ?? $payroll->allowances,
            'deductions'   => $data['deductions'] ?? $payroll->deductions,
            'net_salary'   => $net,
            'notes'        => $data['notes'] ?? $payroll->notes,
        ]);

        return $payroll->load('user:id,name,email');
    }

    public function markPaid(int $id): Payroll
    {
        $payroll = $this->find($id);
        $payroll->update(['status' => 'paid', 'paid_at' => now()]);
        return $payroll->load('user:id,name,email');
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): Payroll
    {
        return Payroll::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
