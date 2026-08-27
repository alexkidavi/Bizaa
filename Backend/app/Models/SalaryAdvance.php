<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'business_id', 'employee_id', 'amount', 'repayment_amount_per_period', 'reason',
        'status', 'requested_by', 'approved_by', 'approved_at', 'disbursed_at', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'repayment_amount_per_period' => 'decimal:2',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    protected $appends = ['repaid_amount', 'outstanding_balance'];

    public function getRepaidAmountAttribute(): float
    {
        return round((float) $this->repayments->sum('amount'), 2);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return round((float) $this->amount - $this->repaid_amount, 2);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(SalaryAdvanceRepayment::class);
    }
}
