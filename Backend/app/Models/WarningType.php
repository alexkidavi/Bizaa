<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarningType extends Model
{
    protected $fillable = ['business_id', 'name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class);
    }
}
