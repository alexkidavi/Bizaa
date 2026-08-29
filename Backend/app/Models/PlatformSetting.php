<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'company_name', 'tagline', 'logo', 'support_email', 'support_phone',
        'whatsapp_number', 'address', 'facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo);
    }
}
