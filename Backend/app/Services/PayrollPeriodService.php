<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\Overtime;
use App\Models\PayrollPeriod;
use App\Models\PayrollSalaryComponent;
use App\Models\PayrollStatutoryRate;
use App\Models\PayrollTaxBand;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Models\SalaryAdvance;
use App\Models\SalaryAdvanceRepayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollPeriodService
{
    protected const RELATIONS = ['generator:id,name', 'approver:id,name', 'finalizer:id,name', 'payer:id,name'];

    public function __construct(protected PayrollSettingService $settingService) {}

    public function list(int $perPage = 50): LengthAwarePaginator
    {
        return PayrollPeriod::where('business_id', Auth::user()->business_id)
            ->withCount('payslips')
            ->with(self::RELATIONS)
            ->orderByDesc('start_date')
            ->paginate($perPage);
    }

    public function find(int $id): PayrollPeriod
    {
        return PayrollPeriod::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    public function findWithPayslips(int $id): PayrollPeriod
    {
        return PayrollPeriod::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(array_merge(self::RELATIONS, [
                'payslips.employee:id,first_name,middle_name,last_name,employee_number',
                'payslips.lines',
            ]))
            ->firstOrFail();
    }

    public function create(array $data): PayrollPeriod
    {
        return PayrollPeriod::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'pay_date' => $data['pay_date'] ?? null,
            'status' => 'draft',
            'generated_by' => Auth::id(),
        ]);
    }

    public function delete(int $id): void
    {
        $period = $this->find($id);
        if (!in_array($period->status, ['draft', 'calculated', 'cancelled'])) {
            throw ValidationException::withMessages([
                'status' => ['Only draft, calculated or cancelled periods can be deleted.'],
            ]);
        }
        $period->delete();
    }

    public function cancel(int $id): PayrollPeriod
    {
        $period = $this->find($id);
        if (!in_array($period->status, ['draft', 'calculated'])) {
            throw ValidationException::withMessages([
                'status' => ['Only draft or calculated periods can be cancelled — an approved period must run its course.'],
            ]);
        }
        $period->update(['status' => 'cancelled']);
        return $period->fresh(self::RELATIONS);
    }

    /*
    |--------------------------------------------------------------------------
    | Calculation
    |--------------------------------------------------------------------------
    */

    public function calculate(int $id): PayrollPeriod
    {
        $period = $this->find($id);
        if (!in_array($period->status, ['draft', 'calculated'])) {
            throw ValidationException::withMessages([
                'status' => ['Only a draft or already-calculated period can be (re)calculated.'],
            ]);
        }

        $businessId = $period->business_id;
        $settings = $this->settingService->getOrCreate();

        $payeBands = $this->latestBandSet(
            PayrollTaxBand::where('business_id', $businessId)->where('is_active', true)
                ->where('effective_date', '<=', $period->end_date)->get()
        );
        $nssfBands = $this->latestStatutorySet($businessId, 'nssf', $period->end_date);
        $shifRate = $this->latestStatutorySet($businessId, 'shif', $period->end_date)->first();
        $housingRate = $this->latestStatutorySet($businessId, 'housing_levy', $period->end_date)->first();

        $components = PayrollSalaryComponent::where('business_id', $businessId)
            ->where('is_active', true)->orderBy('display_order')->get();

        $employees = EmployeeProfile::where('business_id', $businessId)
            ->where('employment_status', 'active')
            ->where('employment_date', '<=', $period->end_date)
            ->get();

        DB::transaction(function () use ($period, $employees, $components, $payeBands, $nssfBands, $shifRate, $housingRate, $settings) {
            $existingIds = Payslip::where('payroll_period_id', $period->id)->pluck('id');
            PayslipLine::whereIn('payslip_id', $existingIds)->delete();
            Payslip::where('payroll_period_id', $period->id)->delete();

            foreach ($employees as $employee) {
                $this->calculateForEmployee($period, $employee, $settings, $components, $payeBands, $nssfBands, $shifRate, $housingRate);
            }

            $period->update(['status' => 'calculated', 'generated_by' => Auth::id()]);
        });

        return $this->findWithPayslips($period->id);
    }

    protected function calculateForEmployee(
        PayrollPeriod $period,
        EmployeeProfile $employee,
        $settings,
        Collection $components,
        Collection $payeBands,
        Collection $nssfBands,
        ?PayrollStatutoryRate $shifRate,
        ?PayrollStatutoryRate $housingRate
    ): Payslip {
        $lines = [];
        $sortOrder = 0;

        [$ratio, $daysPaid, $totalDays] = $this->prorationRatio($period, $employee, $settings);

        $basicSalary = $ratio >= 1 ? (float) $employee->basic_salary : round((float) $employee->basic_salary * $ratio, 2);
        $lines[] = $this->line('earning', 'basic', 'Basic Salary', null, $basicSalary, ['ratio' => $ratio], $sortOrder++);

        $grossEarnings = $basicSalary;
        $taxableEarnings = $basicSalary;
        $otherDeductions = 0;

        foreach ($components as $component) {
            $amount = $component->calculation_type === 'fixed'
                ? (float) $component->amount
                : round($basicSalary * (float) $component->percentage / 100, 2);

            if ($amount <= 0) {
                continue;
            }

            if ($component->type === 'earning') {
                $grossEarnings += $amount;
                if ($component->is_taxable) {
                    $taxableEarnings += $amount;
                }
                $lines[] = $this->line('earning', 'component', $component->name, $component->code, $amount, null, $sortOrder++);
            } else {
                $otherDeductions += $amount;
                $lines[] = $this->line('deduction', 'component', $component->name, $component->code, $amount, null, $sortOrder++);
            }
        }

        [$overtimeHours, $overtimePay] = $this->overtimeFor($employee, $period);
        if ($overtimePay > 0) {
            $grossEarnings += $overtimePay;
            $taxableEarnings += $overtimePay;
            $lines[] = $this->line('earning', 'overtime', 'Overtime', null, $overtimePay, ['hours' => $overtimeHours], $sortOrder++);
        }

        $paye = $this->calculatePaye($taxableEarnings, $payeBands, (float) $settings->personal_relief);
        if ($paye > 0) {
            $lines[] = $this->line('deduction', 'statutory', 'PAYE', 'PAYE', $paye, null, $sortOrder++);
        }

        [$nssfEmployee, $nssfEmployer] = $this->calculateTiered($basicSalary, $nssfBands);
        if ($nssfEmployee > 0) {
            $lines[] = $this->line('deduction', 'statutory', 'NSSF', 'NSSF', $nssfEmployee, null, $sortOrder++);
        }

        [$shifEmployee] = $this->calculateFlatRate($grossEarnings, $shifRate);
        if ($shifEmployee > 0) {
            $lines[] = $this->line('deduction', 'statutory', 'SHIF', 'SHIF', $shifEmployee, null, $sortOrder++);
        }

        [$housingEmployee, $housingEmployer] = $this->calculateFlatRate($grossEarnings, $housingRate);
        if ($housingEmployee > 0) {
            $lines[] = $this->line('deduction', 'statutory', 'Housing Levy', 'HOUSING_LEVY', $housingEmployee, null, $sortOrder++);
        }

        $netBeforeAdvance = $grossEarnings - $paye - $nssfEmployee - $shifEmployee - $housingEmployee - $otherDeductions;
        [$advanceRepayment, $advanceLines] = $this->advanceRepaymentsFor($employee, max(0, $netBeforeAdvance), $sortOrder);
        $lines = array_merge($lines, $advanceLines);
        $sortOrder += count($advanceLines);

        $netPay = $this->round($netBeforeAdvance - $advanceRepayment, $settings->rounding_mode);

        $payslip = Payslip::create([
            'business_id' => $period->business_id,
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'basic_salary' => $basicSalary,
            'working_days' => $totalDays,
            'days_paid' => $daysPaid,
            'overtime_hours' => $overtimeHours,
            'overtime_pay' => $overtimePay,
            'gross_earnings' => $grossEarnings,
            'taxable_income' => $taxableEarnings,
            'paye' => $paye,
            'nssf_employee' => $nssfEmployee,
            'nssf_employer' => $nssfEmployer,
            'shif' => $shifEmployee,
            'housing_levy_employee' => $housingEmployee,
            'housing_levy_employer' => $housingEmployer,
            'other_deductions' => $otherDeductions,
            'advance_repayment' => $advanceRepayment,
            'net_pay' => $netPay,
        ]);

        foreach ($lines as $line) {
            $line['payslip_id'] = $payslip->id;
            PayslipLine::create($line);
        }

        return $payslip;
    }

    /*
    |--------------------------------------------------------------------------
    | Calculation helpers
    |--------------------------------------------------------------------------
    */

    protected function line(string $type, string $source, string $name, ?string $code, float $amount, ?array $meta, int $sortOrder): array
    {
        return ['type' => $type, 'source' => $source, 'name' => $name, 'code' => $code, 'amount' => $amount, 'meta' => $meta, 'sort_order' => $sortOrder];
    }

    /**
     * Ratio of the period this employee is actually paid for, plus the raw
     * day counts behind it — 1.0 (full pay) unless they joined partway
     * through the period, in which case it's prorated per the business's
     * configured proration_method.
     */
    protected function prorationRatio(PayrollPeriod $period, EmployeeProfile $employee, $settings): array
    {
        $start = $period->start_date;
        $end = $period->end_date;
        $joinDate = $employee->employment_date;
        $effectiveStart = $joinDate && $joinDate->gt($start) ? $joinDate : $start;

        if ($effectiveStart->gt($end)) {
            return [0.0, 0, 0];
        }

        if ($settings->proration_method === 'working_days') {
            $totalDays = $this->countWorkingDays($start, $end);
            $paidDays = $this->countWorkingDays($effectiveStart, $end);
        } else {
            $totalDays = $start->diffInDays($end) + 1;
            $paidDays = $effectiveStart->diffInDays($end) + 1;
        }

        $totalDays = max($totalDays, 1);
        $ratio = min(1.0, $paidDays / $totalDays);

        return [$ratio, $paidDays, $totalDays];
    }

    protected function countWorkingDays($start, $end): int
    {
        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (!$cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }
        return $count;
    }

    protected function overtimeFor(EmployeeProfile $employee, PayrollPeriod $period): array
    {
        if (!$employee->user_id) {
            return [0.0, 0.0];
        }

        $records = Overtime::where('user_id', $employee->user_id)
            ->where('status', 'approved')
            ->whereBetween('date', [$period->start_date->toDateString(), $period->end_date->toDateString()])
            ->get();

        $hours = (float) $records->sum('hours');
        $pay = round((float) $records->sum(fn ($o) => $o->hours * $o->rate_per_hour), 2);

        return [$hours, $pay];
    }

    protected function latestBandSet(Collection $bands): Collection
    {
        if ($bands->isEmpty()) {
            return collect();
        }
        $latest = $bands->max('effective_date');
        return $bands->filter(fn ($b) => $b->effective_date->equalTo($latest))->sortBy('lower_limit')->values();
    }

    protected function latestStatutorySet(int $businessId, string $scheme, $onOrBefore): Collection
    {
        $rows = PayrollStatutoryRate::where('business_id', $businessId)
            ->where('scheme', $scheme)->where('is_active', true)
            ->where('effective_date', '<=', $onOrBefore)->get();

        return $this->latestBandSet($rows);
    }

    protected function calculatePaye(float $taxableIncome, Collection $bands, float $personalRelief): float
    {
        $tax = 0;
        foreach ($bands as $band) {
            if ($taxableIncome <= $band->lower_limit) {
                continue;
            }
            $upper = $band->upper_limit !== null ? (float) $band->upper_limit : INF;
            $amountInBand = min($taxableIncome, $upper) - (float) $band->lower_limit;
            if ($amountInBand <= 0) {
                continue;
            }
            $tax += $amountInBand * ((float) $band->rate / 100);
        }
        return round(max(0, $tax - $personalRelief), 2);
    }

    protected function calculateTiered(float $pensionablePay, Collection $bands): array
    {
        $employeeTotal = 0;
        $employerTotal = 0;
        foreach ($bands as $band) {
            if ($pensionablePay <= $band->lower_limit) {
                continue;
            }
            $upper = $band->upper_limit !== null ? (float) $band->upper_limit : INF;
            $amountInBand = min($pensionablePay, $upper) - (float) $band->lower_limit;
            if ($amountInBand <= 0) {
                continue;
            }
            $employeeTotal += $amountInBand * ((float) ($band->employee_rate ?? 0) / 100);
            $employerTotal += $amountInBand * ((float) ($band->employer_rate ?? 0) / 100);
        }
        return [round($employeeTotal, 2), round($employerTotal, 2)];
    }

    protected function calculateFlatRate(float $base, ?PayrollStatutoryRate $rate): array
    {
        if (!$rate || $base <= 0) {
            return [0.0, 0.0];
        }

        $employee = round($base * ((float) ($rate->employee_rate ?? 0)) / 100, 2);
        if ($rate->minimum_amount && $employee > 0 && $employee < $rate->minimum_amount) {
            $employee = (float) $rate->minimum_amount;
        }
        $employer = round($base * ((float) ($rate->employer_rate ?? 0)) / 100, 2);

        return [$employee, $employer];
    }

    /**
     * Deducts against the employee's outstanding salary advances, oldest
     * first, capped so a deduction never exceeds either the advance's
     * remaining balance or the net pay actually available before it —
     * the overpayment guard.
     */
    protected function advanceRepaymentsFor(EmployeeProfile $employee, float $availableNet, int $sortOrderStart): array
    {
        $advances = SalaryAdvance::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->with('repayments')
            ->orderBy('created_at')
            ->get()
            ->filter(fn ($a) => $a->outstanding_balance > 0);

        $total = 0.0;
        $lines = [];
        $sortOrder = $sortOrderStart;
        $remaining = $availableNet;

        foreach ($advances as $advance) {
            if ($remaining <= 0) {
                break;
            }
            $installment = min((float) $advance->repayment_amount_per_period, $advance->outstanding_balance, $remaining);
            $installment = round($installment, 2);
            if ($installment <= 0) {
                continue;
            }
            $total += $installment;
            $remaining -= $installment;
            $lines[] = $this->line('deduction', 'advance', 'Salary Advance Repayment', (string) $advance->id, $installment, ['salary_advance_id' => $advance->id], $sortOrder++);
        }

        return [round($total, 2), $lines];
    }

    protected function round(float $value, string $mode): float
    {
        return match ($mode) {
            'nearest' => round($value),
            'up' => ceil($value),
            'down' => floor($value),
            default => round($value, 2),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow: approve -> finalize -> pay
    |--------------------------------------------------------------------------
    */

    public function approve(int $id): PayrollPeriod
    {
        $period = $this->find($id);
        if ($period->status !== 'calculated') {
            throw ValidationException::withMessages(['status' => ['Only a calculated period can be approved.']]);
        }
        $period->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        return $period->fresh(self::RELATIONS);
    }

    public function finalize(int $id): PayrollPeriod
    {
        $period = $this->find($id);
        if ($period->status !== 'approved') {
            throw ValidationException::withMessages(['status' => ['Only an approved period can be finalized.']]);
        }
        $period->update(['status' => 'finalized', 'finalized_by' => Auth::id(), 'finalized_at' => now()]);
        return $period->fresh(self::RELATIONS);
    }

    /**
     * The only point at which salary advance repayments are actually
     * booked to the ledger (decrementing outstanding_balance) — a
     * recalculated or abandoned draft never leaves a phantom repayment
     * behind, because nothing is booked until money truly moves.
     */
    public function markPaid(int $id): PayrollPeriod
    {
        $period = $this->find($id);
        if ($period->status !== 'finalized') {
            throw ValidationException::withMessages(['status' => ['Only a finalized period can be marked as paid.']]);
        }

        DB::transaction(function () use ($period) {
            $payslips = Payslip::where('payroll_period_id', $period->id)->where('is_excluded', false)->with('lines')->get();

            foreach ($payslips as $payslip) {
                foreach ($payslip->lines->where('source', 'advance') as $line) {
                    $advanceId = $line->meta['salary_advance_id'] ?? $line->code;
                    $advance = SalaryAdvance::find($advanceId);
                    if (!$advance) {
                        continue;
                    }
                    SalaryAdvanceRepayment::create([
                        'salary_advance_id' => $advance->id,
                        'payslip_id' => $payslip->id,
                        'amount' => $line->amount,
                    ]);
                    if ($advance->fresh()->outstanding_balance <= 0) {
                        $advance->update(['status' => 'completed']);
                    }
                }
            }

            $period->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);
        });

        return $period->fresh(self::RELATIONS);
    }

    public function toggleExclude(int $periodId, int $payslipId, bool $excluded): Payslip
    {
        $period = $this->find($periodId);
        if (!in_array($period->status, ['calculated', 'approved'])) {
            throw ValidationException::withMessages(['status' => ['Payslips can only be excluded before finalization.']]);
        }
        $payslip = Payslip::where('id', $payslipId)->where('payroll_period_id', $periodId)->firstOrFail();
        $payslip->update(['is_excluded' => $excluded]);
        return $payslip->fresh();
    }
}
