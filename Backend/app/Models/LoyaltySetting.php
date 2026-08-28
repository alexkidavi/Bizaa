<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltySetting extends Model
{
    protected $fillable = [
        'business_id', 'is_enabled', 'earn_amount_spent', 'earn_points_awarded',
        'point_value', 'min_redeem_points',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'earn_amount_spent' => 'decimal:2',
        'earn_points_awarded' => 'integer',
        'point_value' => 'decimal:4',
        'min_redeem_points' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
