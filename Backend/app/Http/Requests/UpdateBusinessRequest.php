<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email',
            'business_phone' => 'required|string',
            'business_website' => 'nullable|url',
            'business_industry' => 'nullable|string',
            'business_location' => 'nullable|string',
            // Excludes svg: it passes Laravel's generic "image" rule but dompdf's
            // SVG renderer can't reliably rasterize most real-world logo SVGs, so
            // an svg upload here would validate fine and then silently fail to
            // appear on every receipt/payslip/report PDF. webp/bmp still allowed —
            // ReportBranding converts those to PNG at render time (see resolveLogo()).
            'business_logo' => 'nullable|mimes:jpeg,jpg,png,gif,webp,bmp|max:2048',
            'report_brand_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }
}
