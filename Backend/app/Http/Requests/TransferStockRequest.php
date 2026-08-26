<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransferStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->business_id;

        return [
            'from_branch_id' => [
                'required',
                'integer',
                'different:to_branch_id',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
            'to_branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
            'notes' => ['nullable', 'string', 'max:255'],

            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
