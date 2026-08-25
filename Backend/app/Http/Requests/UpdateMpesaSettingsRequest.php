<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMpesaSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mpesa_shortcode' => 'nullable|string|max:20',
            'mpesa_shortcode_type' => 'nullable|string|in:paybill,till',
            'mpesa_passkey' => 'nullable|string|max:1000',
            'mpesa_consumer_key' => 'nullable|string|max:1000',
            'mpesa_consumer_secret' => 'nullable|string|max:1000',
        ];
    }
}
