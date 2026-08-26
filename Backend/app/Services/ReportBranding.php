<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves a business's branding (logo + accent color) into a form the
 * report PDF/Excel templates can use directly — the logo as an embedded
 * base64 data URI (dompdf can't reliably fetch storage:// or relative
 * paths at render time) and a validated hex color with a Bizaa-blue
 * fallback for businesses that haven't set one.
 */
class ReportBranding
{
    protected const DEFAULT_COLOR = '#229DE7';

    public static function forBusiness(?Business $business): array
    {
        return [
            'name'  => $business?->business_name ?? 'Bizaa',
            'color' => self::resolveColor($business?->report_brand_color),
            'logo'  => self::resolveLogo($business?->business_logo),
        ];
    }

    protected static function resolveColor(?string $color): string
    {
        return $color && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : self::DEFAULT_COLOR;
    }

    protected static function resolveLogo(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            if (! Storage::disk('public')->exists($path)) {
                return null;
            }

            $contents = Storage::disk('public')->get($path);
            $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode($contents);
        } catch (\Throwable) {
            return null;
        }
    }
}
