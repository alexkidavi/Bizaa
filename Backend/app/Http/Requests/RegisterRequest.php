<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:100',
            'email'             => 'required|email|unique:users,email',
            'phone'             => 'required|string|max:20',
            'password'          => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
            'business_name'     => 'required|string|max:255',
            'business_email'    => 'required|email',
            'business_phone'    => 'required|string|max:20',
            'business_industry' => 'required|string|max:100',
            'business_location' => 'required|string|max:100',
            'business_website'  => 'nullable|string|max:255',
        ];
    }
}
