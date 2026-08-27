<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollStatutoryRate extends Model
{
    protected $fillable = [
        'business_id', 'scheme', 'effective_date', 'lower_limit', 'upper_limit',
        'employee_rate', 'employer_rate', 'minimum_amount', 'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'lower_limit' => 'decimal:2',
        'upper_limit' => 'decimal:2',
        'employee_rate' => 'decimal:2',
        'employer_rate' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
