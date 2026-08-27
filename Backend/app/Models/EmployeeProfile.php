<?php

namespace App\Models;

use App\Traits\LogsHrmChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProfile extends Model
{
    use LogsHrmChanges, SoftDeletes;

    protected $fillable = [
        'business_id', 'user_id', 'employee_number',
        'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
        'national_id', 'address',
        'employment_date', 'department_id', 'designation', 'employment_type',
        'employment_status', 'branch_id', 'manager_id', 'basic_salary',
        'bank_name', 'bank_account_number', 'bank_branch',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'kra_pin', 'nssf_number', 'shif_number', 'housing_benefit',
        'photo', 'documents', 'notes', 'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'employment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'documents' => 'array',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} " . ($this->middle_name ? "{$this->middle_name} " : '') . $this->last_name);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'employee_id');
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class, 'employee_id');
    }

    public function resignations(): HasMany
    {
        return $this->hasMany(Resignation::class, 'employee_id');
    }

    public function terminations(): HasMany
    {
        return $this->hasMany(Termination::class, 'employee_id');
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class, 'employee_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'employee_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'employee_id');
    }

    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class, 'employee_id');
    }
}
