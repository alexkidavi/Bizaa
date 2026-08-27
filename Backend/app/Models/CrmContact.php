<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'company_id', 'customer_id', 'name', 'email', 'phone',
        'source_id', 'lifecycle_stage', 'assigned_to', 'notes', 'created_by',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CrmCompany::class, 'company_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(CrmSource::class, 'source_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CrmTag::class, 'crm_contact_tag', 'contact_id', 'tag_id')->withTimestamps();
    }

    public function deals(): HasMany
    {
        return $this->hasMany(CrmDeal::class, 'contact_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'contact_id');
    }
}
