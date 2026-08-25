<?php

namespace App\Http\Requests;

use App\Policies\SupplierPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        $policy = new SupplierPolicy();

        return $policy->create($user);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'national_id' => ['nullable','max:255'],
            'company_name' => ['nullable','max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['exists:branches,id'],
        ];
    }

  
}