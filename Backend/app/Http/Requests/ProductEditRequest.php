<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProductEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

  public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],

        'barcode' => ['nullable', 'string', 'max:100'],

        'category_id' => ['required', 'integer', 'exists:categories,id'],
        'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
        'unit_id' => ['required', 'integer', 'exists:units,id'],

        'buying_price' => ['required', 'numeric', 'min:0'],
        'selling_price' => ['required', 'numeric', 'min:0'],
        'wholesale_price' => ['nullable', 'numeric', 'min:0'],

        'tax_type' => ['required', 'in:vat,non_vat'],

        'item_class_code' => ['nullable', 'string', 'max:20'],
        'item_type_code' => ['nullable', 'in:1,2,3'],
        'tax_type_code' => ['nullable', 'in:A,B,C,D,E'],
        'package_unit_code' => ['nullable', 'string', 'max:10'],
        'quantity_unit_code' => ['nullable', 'string', 'max:10'],

        'opening_stock' => ['nullable', 'integer', 'min:0'],
        'reorder_level' => ['nullable', 'integer', 'min:0'],

        'for_sale' => ['boolean'],
        'for_purchase' => ['boolean'],

        'branch_ids' => ['required', 'array', 'min:1'],
        'branch_ids.*' => ['integer', 'exists:branches,id'],

        'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

        'variants' => ['nullable', 'array'],
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