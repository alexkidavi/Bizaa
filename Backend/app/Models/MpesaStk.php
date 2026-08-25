<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaStk extends Model
{
    use HasFactory;

    protected $table = 'mpesa_stks';

    protected $fillable = [
        'business_id',
        'subscription_plan_id',
        'merchant_request_id',
        'checkout_request_id',
        'phone_number',
        'amount',
        'reference',
        'transaction_desc',
        'response_code',
        'response_description',
        'customer_message',
        'status',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'integer',
        'status' => 'integer',
        'raw_response' => 'array', // 🔥 important for JSON storage
    ];
}