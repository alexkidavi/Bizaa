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

    /** dompdf only reliably renders these — everything else (webp, bmp, svg)
     *  either isn't supported at all or is too inconsistent to trust. */
    protected const PDF_SAFE_MIMES = ['image/png', 'image/jpeg', 'image/gif'];

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

            // A logo already stored in a format dompdf can't render (older
            // uploads made before webp/svg were excluded from validation,
            // or svg uploaded before that restriction existed) would
            // otherwise silently fail to appear on every PDF. Convert on
            // the fly instead of requiring the business to re-upload.
            if (! in_array($mime, self::PDF_SAFE_MIMES, true)) {
                $converted = self::convertToPng($contents, $mime);
                if ($converted === null) {
                    return null;
                }
                $contents = $converted;
                $mime = 'image/png';
            }

            return 'data:' . $mime . ';base64,' . base64_encode($contents);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Best-effort raster conversion via GD. Returns null (never throws) if
     *  the server's GD build can't read the source format — svg included,
     *  GD has no vector rasterizer at all so that case always falls through. */
    protected static function convertToPng(string $contents, string $mime): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $info = gd_info();
        $canRead = match ($mime) {
            'image/webp' => $info['WebP Support'] ?? false,
            'image/bmp', 'image/x-ms-bmp' => $info['BMP Support'] ?? false,
            default => false,
        };

        if (! $canRead) {
            return null;
        }

        $image = @imagecreatefromstring($contents);
        if (! $image) {
            return null;
        }

        imagesavealpha($image, true);
        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return $png ?: null;
    }
}
