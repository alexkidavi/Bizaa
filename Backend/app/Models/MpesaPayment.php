<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaPayment extends Model
{
    protected $fillable = [
        'business_id',
        'amount',
        'mpesa_receipt_number',
        'phonenumber',
        'transaction_date',
        'checkout_request_id',
        'order_id',
        'customer_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'transaction_date' => 'datetime',
    ];
}