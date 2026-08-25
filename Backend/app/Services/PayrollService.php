<?php

namespace App\Services;

use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

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
        $staff = User::where('business_id', $businessId)
            ->whereNotNull('registered_by')
            ->where('is_active', true)
            ->get();

        $created = [];
        foreach ($staff as $user) {
            $existing = Payroll::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existing) {
                continue;
            }

            $overtimePay = Overtime::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->sum(fn ($ot) => $ot->hours * $ot->rate_per_hour);

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
                'created_by'   => Auth::id(),
            ]);
        }

        return $created;
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
