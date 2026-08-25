<?php

namespace App\Http\Requests;

use App\Policies\BrandPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CustomerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['required', 'string', 'max:20'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['exists:branches,id'],
        ];
    }

  
}