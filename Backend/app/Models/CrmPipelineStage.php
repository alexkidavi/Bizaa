<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmPipelineStage extends Model
{
    protected $fillable = [
        'business_id', 'name', 'display_order', 'is_won', 'is_lost', 'default_probability',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'default_probability' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(CrmDeal::class, 'pipeline_stage_id');
    }
}
