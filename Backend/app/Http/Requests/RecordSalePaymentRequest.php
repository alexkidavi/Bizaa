<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordSalePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method'     => ['nullable', 'string', 'max:50'],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'note'       => ['nullable', 'string'],
            'mpesa_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
