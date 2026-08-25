<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
class StorePurchaseRequest extends FormRequest
{
    /**
     * Authorize request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            // ── Header ─────────────────────────────────────────────
            'supplier_id'       => ['required', 'integer', 'exists:suppliers,id'],
            'reference'         => ['nullable', 'string', 'max:100'],
            'purchase_date'     => ['required', 'date'],
            'notes'             => ['nullable', 'string', 'max:2000'],

            // ── Financials ──────────────────────────────────────────
            'subtotal'          => ['required', 'numeric', 'min:0'],
            'total_discount'    => ['required', 'numeric', 'min:0'],
            'total_tax'         => ['required', 'numeric', 'min:0'],
            'grand_total'       => ['required', 'numeric', 'min:0'],

            // ── Payment ─────────────────────────────────────────────
            'payment_type'      => ['required', 'in:full,partial,none'],
            'paid_amount'       => [
                'required_unless:payment_type,none',
                'nullable',
                'numeric',
                'min:0'
            ],

            'payment_method'    => [
                'required_unless:payment_type,none',
                'nullable',
                'string',
                'max:50'
            ],

            'payment_reference' => ['nullable', 'string', 'max:100'],

            // ── Branches ────────────────────────────────────────────
            'branches'             => ['required', 'array', 'min:1'],
            'branches.*.branch_id' => ['required', 'integer', 'exists:branches,id'],
            'branches.*.subtotal'  => ['required', 'numeric', 'min:0'],

            // ── Items ───────────────────────────────────────────────
            'branches.*.items' => ['required', 'array', 'min:1'],

            'branches.*.items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id'
            ],

            'branches.*.items.*.quantity' => [
                'required',
                'numeric',
                'min:0.0001'
            ],

            'branches.*.items.*.unit_price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'branches.*.items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],

            'branches.*.items.*.tax' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],

            'branches.*.items.*.line_total' => [
                'required',
                'numeric',
                'min:0'
            ],

            'branches.*.items.*.expiry_date' => [
                'nullable',
                'date'
            ],

            'branches.*.items.*.batch_number' => [
                'nullable',
                'string',
                'max:100'
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [

            'supplier_id.required' => 'Please select a supplier.',
            'supplier_id.exists'   => 'Selected supplier does not exist.',

            'purchase_date.required' => 'Purchase date is required.',

            'payment_type.in' => 'Invalid payment type.',

            'paid_amount.required_unless' =>
                'Amount paid is required.',

            'payment_method.required_unless' =>
                'Payment method is required.',

            'branches.required' =>
                'At least one branch must be selected.',

            'branches.min' =>
                'At least one branch must be selected.',

            'branches.*.branch_id.required' =>
                'Branch ID is required.',

            'branches.*.branch_id.exists' =>
                'A selected branch does not exist.',

            'branches.*.items.required' =>
                'Each branch must have at least one item.',

            'branches.*.items.min' =>
                'Each branch must have at least one item.',

            'branches.*.items.*.product_id.required' =>
                'Product is required for all items.',

            'branches.*.items.*.product_id.exists' =>
                'A selected product does not exist.',

            'branches.*.items.*.quantity.required' =>
                'Quantity is required for all items.',

            'branches.*.items.*.quantity.min' =>
                'Quantity must be greater than zero.',

            'branches.*.items.*.unit_price.required' =>
                'Unit price is required for all items.',

            'branches.*.items.*.unit_price.min' =>
                'Unit price cannot be negative.',

            'branches.*.items.*.discount.max' =>
                'Discount cannot exceed 100%.',

            'branches.*.items.*.tax.max' =>
                'Tax cannot exceed 100%.',
        ];
    }

    /**
     * Prepare request data before validation.
     */
   protected function prepareForValidation(): void
{
    $branches = $this->branches ?? [];

    $branches = array_map(function ($branch) {

        $branch['branch_id'] = (int) ($branch['branch_id'] ?? 0);

        $branch['subtotal'] = (float) ($branch['subtotal'] ?? 0);

        $branch['items'] = array_map(function ($item) {

            // decrypt product id
            if (!empty($item['product_id'])) {

               try {

    $item['product_id'] = (int) Crypt::decryptString(
        $item['product_id']
    );

} catch (\Exception $e) {

    info($e->getMessage());

    $item['product_id'] = null;
}
            }

            return [

                'product_id' => $item['product_id'],

                'quantity' => (float) ($item['quantity'] ?? 0),

                'unit_price' => (float) ($item['unit_price'] ?? 0),

                'discount' => (float) ($item['discount'] ?? 0),

                'tax' => (float) ($item['tax'] ?? 0),

                'line_total' => (float) ($item['line_total'] ?? 0),

                'expiry_date' => $item['expiry_date'] ?? null,

                'batch_number' => $item['batch_number'] ?? null,
            ];

        }, $branch['items'] ?? []);

        return $branch;

    }, $branches);

    $this->merge([

        'supplier_id' => (int) ($this->supplier_id ?? 0),

        'subtotal' => (float) ($this->subtotal ?? 0),

        'total_discount' => (float) ($this->total_discount ?? 0),

        'total_tax' => (float) ($this->total_tax ?? 0),

        'grand_total' => (float) ($this->grand_total ?? 0),

        'paid_amount' => $this->paid_amount !== null
            ? (float) $this->paid_amount
            : null,

        'branches' => $branches,
    ]);
}
}
