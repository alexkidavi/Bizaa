<?php

namespace App\Http\Requests;

use App\Http\Helpers\PhoneHelper;
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
            // Normalized in prepareForValidation() to the same 254XXXXXXXXX
            // format AuthService::register() stores, so this actually
            // catches a duplicate instead of comparing raw vs. normalized.
            'phone'             => 'required|string|max:20|unique:users,phone',
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
            // Where the signup happened — 'web' today, a future mobile app
            // would send 'mobile'. Optional so older/other clients that
            // don't send it yet aren't broken.
            'source'            => 'nullable|string|max:30',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'An account with this email already exists. Try logging in instead.',
            'phone.unique' => 'This phone number is already registered to another account.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->phone) {
            $this->merge(['phone' => PhoneHelper::normalize($this->phone)]);
        }
        if ($this->business_phone) {
            $this->merge(['business_phone' => PhoneHelper::normalize($this->business_phone)]);
        }
    }
}
