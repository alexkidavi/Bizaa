<?php

namespace App\Http\Requests;

use App\Policies\CategoryPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        $policy = new CategoryPolicy();

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
            'name.required' => 'Category name is required.',
            'name.max' => 'Category name cannot exceed 255 characters.',

            'description.max' => 'Description cannot exceed 1000 characters.',

            'branch_ids.required' => 'Please select at least one branch.',
            'branch_ids.array' => 'Branches must be a valid list.',

            'branch_ids.*.exists' => 'One or more selected branches are invalid.',
        ];
    }
}