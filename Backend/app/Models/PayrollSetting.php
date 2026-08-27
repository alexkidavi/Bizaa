<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSetting extends Model
{
    protected $fillable = [
        'business_id', 'pay_frequency', 'working_days_per_month', 'standard_hours_per_day',
        'overtime_multiplier', 'rounding_mode', 'proration_method', 'personal_relief', 'currency',
    ];

    protected $casts = [
        'standard_hours_per_day' => 'decimal:2',
        'overtime_multiplier' => 'decimal:2',
        'personal_relief' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
