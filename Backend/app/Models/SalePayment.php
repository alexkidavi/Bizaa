<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'sale_id',
        'method',
        'amount',
        'reference',
        'note',
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
}