<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ================= BASIC =================
            'name' => ['required', 'string', 'max:255'],
 'product_code' => [
    'required',
    'string',
    'max:100',
    Rule::unique('products', 'product_code')
        ->ignore($this->route('id')),
],
            'barcode' => ['nullable', 'string', 'max:100'],

            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],

            // ================= PRICING =================
            'buying_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],

            'tax_type' => ['required', 'in:vat,non_vat'],

            // ================= KRA eTIMS (DigiTax) =================
            'item_class_code' => ['nullable', 'string', 'max:20'],
            'item_type_code' => ['nullable', 'in:1,2,3'],
            'tax_type_code' => ['nullable', 'in:A,B,C,D,E'],
            'package_unit_code' => ['nullable', 'string', 'max:10'],
            'quantity_unit_code' => ['nullable', 'string', 'max:10'],

            // ================= INVENTORY =================
            'opening_stock' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],

            // ================= SETTINGS =================
            'for_sale' => ['boolean'],
            'for_purchase' => ['boolean'],

            // ================= BRANCHES =================
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],

            // ================= IMAGE =================
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // ================= VARIANTS =================
            'variants' => ['nullable', 'array'],

            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.code' => ['required_with:variants', 'string', 'max:100'],

            'variants.*.buying_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.selling_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],

            // parent_id will be handled in controller
            'variants.*.parent_id' => ['nullable', 'integer', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'product_code.required' => 'Product code is required',
            'category_id.required' => 'Category is required',
            'unit_id.required' => 'Unit is required',

            'branch_ids.required' => 'At least one branch is required',
            'branch_ids.min' => 'Select at least one branch',

            'selling_price.required' => 'Selling price is required',
            'buying_price.required' => 'Buying price is required',
        ];
    }
}