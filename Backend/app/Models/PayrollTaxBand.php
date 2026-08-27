<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTaxBand extends Model
{
    protected $fillable = [
        'business_id', 'effective_date', 'lower_limit', 'upper_limit', 'rate', 'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'lower_limit' => 'decimal:2',
        'upper_limit' => 'decimal:2',
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
