<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'name', 'industry', 'phone', 'email', 'address', 'website', 'notes', 'created_by',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CrmContact::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
