<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'business_id', 'name', 'days_allowed', 'description',
        'accrual_rule', 'accrual_rate', 'carry_forward_allowed', 'max_carry_forward_days',
        'gender_eligibility', 'requires_documentation', 'requires_approval', 'is_paid', 'is_active',
    ];

    protected $casts = [
        'accrual_rate' => 'decimal:2',
        'carry_forward_allowed' => 'boolean',
        'requires_documentation' => 'boolean',
        'requires_approval' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
