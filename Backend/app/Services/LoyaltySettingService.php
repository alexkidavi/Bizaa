<?php

namespace App\Services;

use App\Models\LoyaltySetting;
use Illuminate\Support\Facades\Auth;

class LoyaltySettingService
{
    /**
     * One settings row per business, created lazily with schema defaults
     * the first time it's needed — no seeding step required.
     */
    public function getOrCreate(): LoyaltySetting
    {
        $settings = LoyaltySetting::firstOrCreate(['business_id' => Auth::user()->business_id]);

        // firstOrCreate's in-memory model only carries the attribute it
        // explicitly set (business_id) — schema defaults for every other
        // column are applied by the DB on insert and never make it back
        // into this instance without a re-fetch.
        return $settings->wasRecentlyCreated ? $settings->fresh() : $settings;
    }

    public function update(array $data): LoyaltySetting
    {
        $settings = $this->getOrCreate();
        $settings->update([
            'is_enabled' => $data['is_enabled'] ?? $settings->is_enabled,
            'earn_amount_spent' => $data['earn_amount_spent'] ?? $settings->earn_amount_spent,
            'earn_points_awarded' => $data['earn_points_awarded'] ?? $settings->earn_points_awarded,
            'point_value' => $data['point_value'] ?? $settings->point_value,
            'min_redeem_points' => $data['min_redeem_points'] ?? $settings->min_redeem_points,
        ]);
        return $settings->fresh();
    }
}
