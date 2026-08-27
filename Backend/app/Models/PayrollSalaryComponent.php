<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSalaryComponent extends Model
{
    protected $fillable = [
        'business_id', 'name', 'code', 'type', 'calculation_type',
        'amount', 'percentage', 'is_taxable', 'is_active', 'display_order', 'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
