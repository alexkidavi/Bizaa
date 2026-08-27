<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmCampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id', 'contact_id', 'status', 'provider_message_id', 'error', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CrmCampaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }
}
