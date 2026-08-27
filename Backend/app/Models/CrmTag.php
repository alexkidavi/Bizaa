<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CrmTag extends Model
{
    protected $fillable = ['business_id', 'name', 'color'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(CrmContact::class, 'crm_contact_tag', 'tag_id', 'contact_id')->withTimestamps();
    }
}
