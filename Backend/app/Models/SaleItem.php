<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'note',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    /*
    |-----------------------------------
    | Relationships
    |-----------------------------------
    */

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /*
    |-----------------------------------
    | Helpers
    |-----------------------------------
    */

    public function getLineTotalAttribute()
    {
        $base = $this->unit_price * $this->quantity;
        return $base - ($base * ($this->discount / 100));
    }
}