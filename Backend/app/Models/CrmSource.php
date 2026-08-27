<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmSource extends Model
{
    protected $fillable = ['business_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CrmContact::class, 'source_id');
    }
}
