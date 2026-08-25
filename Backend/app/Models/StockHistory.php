<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
protected $fillable = [
    'product_id',
    'branch_id',
    'business_id',
    'reference',
    'user_id',
    'type',
    'action',
    'quantity',
    'notes',
    'digitax_status',
    'digitax_error',
    'digitax_synced_at',
];

protected $casts = [
    'digitax_synced_at' => 'datetime',
];
}
