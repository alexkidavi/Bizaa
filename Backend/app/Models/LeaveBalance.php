<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveBalance extends Model
{
    protected $fillable = [
        'business_id', 'employee_id', 'leave_type_id', 'year',
        'opening_balance', 'accrued_days', 'used_days', 'pending_days',
        'carried_forward_days', 'adjustments',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'accrued_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
        'carried_forward_days' => 'decimal:2',
        'adjustments' => 'decimal:2',
    ];

    protected $appends = ['closing_balance'];

    public function getClosingBalanceAttribute(): float
    {
        return round(
            (float) $this->opening_balance
            + (float) $this->accrued_days
            + (float) $this->carried_forward_days
            + (float) $this->adjustments
            - (float) $this->used_days
            - (float) $this->pending_days,
            2
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function adjustmentHistory(): HasMany
    {
        return $this->hasMany(LeaveBalanceAdjustment::class);
    }
}
