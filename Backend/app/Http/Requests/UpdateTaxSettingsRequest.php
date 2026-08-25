<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'kra_pin' => 'nullable|string|max:50',
            'tax_api_key' => 'nullable|string|max:1000',
        ];
    }
}
