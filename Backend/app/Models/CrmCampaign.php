<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmCampaign extends Model
{
    protected $fillable = [
        'business_id', 'name', 'channel', 'subject', 'message', 'filters',
        'status', 'sent_at', 'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'sent_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CrmCampaignRecipient::class, 'campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
