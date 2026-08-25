<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDigitaxSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'digitax_api_key' => 'nullable|string|max:1000',
            'digitax_environment' => 'nullable|string|in:sandbox,production',
            'digitax_enabled' => 'boolean',
        ];
    }
}
