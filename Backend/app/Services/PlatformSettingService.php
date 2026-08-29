<?php

namespace App\Services;

use App\Models\PlatformSetting;

class PlatformSettingService
{
    /**
     * One row total, created lazily with schema defaults — same
     * getOrCreate() shape as LoyaltySettingService/PayrollSettingService,
     * just without a business_id scope since this is platform-wide.
     */
    public function getOrCreate(): PlatformSetting
    {
        $settings = PlatformSetting::first();
        if ($settings) {
            return $settings;
        }

        return PlatformSetting::create([
            'company_name' => 'Bizaa',
        ]);
    }

    public function update(array $data): PlatformSetting
    {
        $settings = $this->getOrCreate();
        $settings->update($data);
        return $settings->fresh();
    }
}
