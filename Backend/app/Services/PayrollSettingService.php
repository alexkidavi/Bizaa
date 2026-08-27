<?php

namespace App\Services;

use App\Models\PayrollSetting;
use Illuminate\Support\Facades\Auth;

class PayrollSettingService
{
    /**
     * One settings row per business, created lazily with schema defaults
     * the first time it's needed — no seeding step required.
     */
    public function getOrCreate(): PayrollSetting
    {
        $settings = PayrollSetting::firstOrCreate(['business_id' => Auth::user()->business_id]);

        // firstOrCreate's in-memory model only carries the attribute we
        // explicitly set (business_id) — the schema defaults for every
        // other column are applied by the DB on insert and never make it
        // back into this instance without a re-fetch. Without this, the
        // very first call for a new business would return blank fields.
        return $settings->wasRecentlyCreated ? $settings->fresh() : $settings;
    }

    public function update(array $data): PayrollSetting
    {
        $settings = $this->getOrCreate();
        $settings->update([
            'pay_frequency' => $data['pay_frequency'] ?? $settings->pay_frequency,
            'working_days_per_month' => $data['working_days_per_month'] ?? $settings->working_days_per_month,
            'standard_hours_per_day' => $data['standard_hours_per_day'] ?? $settings->standard_hours_per_day,
            'overtime_multiplier' => $data['overtime_multiplier'] ?? $settings->overtime_multiplier,
            'rounding_mode' => $data['rounding_mode'] ?? $settings->rounding_mode,
            'proration_method' => $data['proration_method'] ?? $settings->proration_method,
            'personal_relief' => $data['personal_relief'] ?? $settings->personal_relief,
            'currency' => $data['currency'] ?? $settings->currency,
        ]);
        return $settings->fresh();
    }
}
