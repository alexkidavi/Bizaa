<?php

namespace App\Http\Requests;

use App\Policies\UnitPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        $policy = new UnitPolicy();

        return $policy->create($user);
    }

    public function rules(): array
    {
        return [
            'abbreviation' => 'required|string|max:255',
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
              'abbreviation.required' => 'Unit abbreviation is required.',
            'name.required' => 'Unit name is required.',
            'name.max' => 'Unit name cannot exceed 255 characters.',

            'description.max' => 'Description cannot exceed 1000 characters.',

            'branch_ids.required' => 'Please select at least one branch.',
            'branch_ids.array' => 'Branches must be a valid list.',

            'branch_ids.*.exists' => 'One or more selected branches are invalid.',
        ];
    }
}