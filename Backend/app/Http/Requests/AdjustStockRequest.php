<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->business_id;

        return [
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
            'quantity' => ['required', 'numeric', 'min:0'],
            'action'   => ['required', 'string', Rule::in(['set', 'add', 'remove'])],
            'reason'   => ['nullable', 'string', 'max:255'],
        ];
    }
}
