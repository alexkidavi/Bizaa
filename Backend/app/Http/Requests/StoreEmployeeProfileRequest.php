<?php

namespace App\Http\Requests;

use App\Http\Helpers\PhoneHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreEmployeeProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->business_id;
        $linkingExisting = !empty($this->user_id);

        return [
            // Either link an existing, not-yet-profiled login (user_id) or
            // create a brand new one right here (email/phone/role_id) —
            // exactly one of the two paths, never both required at once.
            'user_id' => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('business_id', $businessId),
                Rule::unique('employee_profiles', 'user_id'),
            ],
            'email' => [
                Rule::requiredIf(!$linkingExisting), 'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->where('business_id', $businessId),
            ],
            'phone' => [
                Rule::requiredIf(!$linkingExisting), 'nullable', 'string',
                'regex:/^254[17]\d{8}$/',
                Rule::unique('users', 'phone'),
            ],
            // Not business-scoped — matches StoreStaffRequest's existing
            // role_id rule. Some seeded roles carry business_id 0 rather
            // than the tenant's own id, so a scoped check here would wrongly
            // reject roles the Staff form itself accepts without issue.
            'role_id' => [Rule::requiredIf(!$linkingExisting), 'nullable', 'integer', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],

            'employee_number' => [
                'nullable', 'string', 'max:50',
                Rule::unique('employee_profiles')->where('business_id', $businessId),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{5,20}$/'],
            'address' => ['nullable', 'string', 'max:500'],

            'employment_date' => ['required', 'date'],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('business_id', $businessId)],
            'designation' => ['nullable', 'string', 'max:150'],
            'employment_type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'intern', 'casual'])],
            'employment_status' => ['nullable', Rule::in(['active', 'on_leave', 'suspended', 'resigned', 'terminated', 'retired'])],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('business_id', $businessId)],
            'manager_id' => ['nullable', Rule::exists('employee_profiles', 'id')->where('business_id', $businessId)],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],

            'bank_name' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'regex:/^\d{5,20}$/'],
            'bank_branch' => ['nullable', 'string', 'max:150'],

            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'regex:/^254[17]\d{8}$/'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],

            'kra_pin' => ['nullable', 'string', 'regex:/^[A-Za-z]\d{9}[A-Za-z]$/'],
            'nssf_number' => ['nullable', 'string', 'max:30'],
            'shif_number' => ['nullable', 'string', 'max:30'],
            'housing_benefit' => ['nullable', 'string', 'max:500'],

            'photo' => ['nullable', 'image', 'max:2048'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A staff member with this email already exists in your business.',
            'phone.unique' => 'This phone number is already registered to another account.',
            'phone.regex' => 'Enter a valid Kenyan phone number.',
            'emergency_contact_phone.regex' => 'Enter a valid Kenyan phone number.',
            'national_id.regex' => 'National ID / Passport should be 5-20 letters or numbers.',
            'kra_pin.regex' => 'Enter a valid KRA PIN, e.g. A012345678Z.',
            'bank_account_number.regex' => 'Bank account number should be 5-20 digits.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->phone) {
            $this->merge(['phone' => PhoneHelper::normalize($this->phone)]);
        }
        if ($this->emergency_contact_phone) {
            $this->merge(['emergency_contact_phone' => PhoneHelper::normalize($this->emergency_contact_phone)]);
        }
    }
}
