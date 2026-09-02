<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
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
            'payment_method' => ['required', 'string', 'max:50'],
            'customer_id'    => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
            'reference' => ['nullable', 'string', 'max:255'],

            // Offline POS. The client sends `client_sale_id` on every sale
            // (idempotency key); `is_offline` + `client_created_at` are only
            // set when the sale was queued in the outbox while disconnected.
            'client_sale_id'    => ['nullable', 'uuid'],
            'is_offline'        => ['nullable', 'boolean'],
            'client_created_at' => ['nullable', 'date'],

            'subtotal'  => ['required', 'numeric', 'min:0'],
            'discount'  => ['nullable', 'numeric', 'min:0'],
            'tax'       => ['nullable', 'numeric', 'min:0'],
            'total'     => ['required', 'numeric', 'min:0'],

            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount'   => ['nullable', 'numeric', 'min:0'],
            'items.*.note'       => ['nullable', 'string'],

            'payments'          => ['nullable', 'array'],
            'payments.*.method' => ['required_with:payments', 'string', 'max:50'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
            'payments.*.note'   => ['nullable', 'string'],

            'paid_amount' => ['nullable', 'numeric', 'min:0'],

            // Single-payment M-Pesa confirmation from the POS modal
            // (STK push, synced transaction, or manual code entry).
            'mpesa_code'   => ['nullable', 'string', 'max:50'],
            'mpesa_phone'  => ['nullable', 'string', 'max:20'],
            'mpesa_method' => ['nullable', 'string', 'max:50'],
        ];
    }
}
