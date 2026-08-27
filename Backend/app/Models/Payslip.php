<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    protected $fillable = [
        'business_id', 'payroll_period_id', 'employee_id',
        'basic_salary', 'working_days', 'days_paid', 'overtime_hours', 'overtime_pay',
        'gross_earnings', 'taxable_income', 'paye', 'nssf_employee', 'nssf_employer',
        'shif', 'housing_levy_employee', 'housing_levy_employer', 'other_deductions',
        'advance_repayment', 'net_pay', 'is_excluded', 'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'gross_earnings' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'paye' => 'decimal:2',
        'nssf_employee' => 'decimal:2',
        'nssf_employer' => 'decimal:2',
        'shif' => 'decimal:2',
        'housing_levy_employee' => 'decimal:2',
        'housing_levy_employer' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'advance_repayment' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'is_excluded' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayslipLine::class)->orderBy('sort_order');
    }
}
