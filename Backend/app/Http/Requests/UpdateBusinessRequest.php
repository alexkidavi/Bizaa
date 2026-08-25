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
            'business_logo' => 'nullable|image|max:2048',
        ];
    }
}
