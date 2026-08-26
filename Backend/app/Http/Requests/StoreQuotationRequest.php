<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = Auth::user()->business_id;

        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],

            'quotation_number'    => ['nullable', 'string', 'max:100'],
            'status'              => ['required', 'in:draft,sent,accepted,rejected,expired,converted'],

            'issue_date'          => ['required', 'date'],
            'expiry_date'         => ['required', 'date', 'after_or_equal:issue_date'],

            'currency'            => ['nullable', 'string', 'size:3'],
            'notes'               => ['nullable', 'string', 'max:2000'],

            'subtotal'            => ['required', 'numeric', 'min:0'],
            'total_discount'      => ['required', 'numeric', 'min:0'],
            'total_tax'           => ['required', 'numeric', 'min:0'],
            'grand_total'         => ['required', 'numeric', 'min:0'],

            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('business_id', $businessId)),
            ],
            'items.*.description'       => ['nullable', 'string', 'max:500'],
            'items.*.quantity'          => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price'        => ['required', 'numeric', 'min:0'],
            'items.*.discount'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax'               => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.line_total'        => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->decryptOrNull($this->customer_id),
            'branch_id'   => $this->decryptOrNull($this->branch_id),

            'subtotal'       => (float) ($this->subtotal ?? 0),
            'total_discount' => (float) ($this->total_discount ?? 0),
            'total_tax'      => (float) ($this->total_tax ?? 0),
            'grand_total'    => (float) ($this->grand_total ?? 0),

            'items' => $this->normalizeItems(),
        ]);
    }

    private function normalizeItems(): array
    {
        return collect($this->items ?? [])
            ->map(function ($item) {
                return [
                    'product_id'  => $this->decryptOrNull($item['product_id'] ?? null),
                    'description' => $item['description'] ?? null,

                    'quantity'    => (float) ($item['quantity'] ?? 0),
                    'unit_price'  => (float) ($item['unit_price'] ?? 0),
                    'discount'    => (float) ($item['discount'] ?? 0),
                    'tax'         => (float) ($item['tax'] ?? 0),
                    'line_total'  => (float) ($item['line_total'] ?? 0),
                ];
            })
            ->toArray();
    }

    private function decryptOrNull($value)
    {
        if ($value === null) {
            return null;
        }

        // Already numeric → safe
        if (is_numeric($value)) {
            return (int) $value;
        }

        try {
            return (int) Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}