<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StockTakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->business_id;

        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'branch_ids'   => ['required', 'array', 'min:1'],
            'branch_ids.*' => [
                'integer',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],

            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
        ];
    }
}
