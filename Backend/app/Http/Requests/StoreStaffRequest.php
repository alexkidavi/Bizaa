<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'phone'    => ['required', 'string', 'max:30'],
            'role_id'  => ['required', 'integer', 'exists:roles,id'],

            // Nullable on both create and update: StaffService::create()
            // generates and returns a random one-time password when this
            // is omitted, rather than requiring the caller to invent one.
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Full name is required.',
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'phone.required'    => 'Phone number is required.',
            'role_id.required'  => 'Please assign a role.',
            'role_id.exists'    => 'The selected role does not exist.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 8 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Trim whitespace
        $this->merge([
            'name'  => trim($this->name ?? ''),
            'email' => strtolower(trim($this->email ?? '')),
            'phone' => trim($this->phone ?? ''),
        ]);
    }
}
