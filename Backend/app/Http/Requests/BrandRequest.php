<?php

namespace App\Http\Requests;

use App\Policies\BrandPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        $policy = new BrandPolicy();

        return $policy->create($user);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',

            'description' => 'nullable|string|max:1000',

            // branches from multiselect
            'branch_ids' => 'required|array',
            'branch_ids.*' => 'integer|exists:branches,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Brand name is required.',
            'name.max' => 'Brand name cannot exceed 255 characters.',

            'description.max' => 'Description cannot exceed 1000 characters.',

            'branch_ids.required' => 'Please select at least one branch.',
            'branch_ids.array' => 'Branches must be a valid list.',

            'branch_ids.*.exists' => 'One or more selected branches are invalid.',
        ];
    }
}