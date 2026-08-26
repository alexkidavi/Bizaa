<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveStockCountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.item_id'       => ['required', 'integer'],
            'items.*.counted_stock' => ['required', 'numeric', 'min:0'],
        ];
    }
}
