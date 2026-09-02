<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'business_id',
        'user_id',
        'reference',
        'branch_id',
        'payment_method',
        'customer_id',
        'subtotal',
        'discount',
        'tax',
        'total',

        // Offline POS (see 2026_09_02_000001 migration)
        'client_sale_id',
        'is_offline',
        'client_created_at',
        'synced_at',
        'stock_shortfall',

        'digitax_sale_id',
        'kra_receipt_number',
        'kra_invoice_number',
        'kra_serial_number',
        'receipt_signature',
        'etims_url',
        'offline_url',
        'digitax_status',
        'digitax_error',
        'digitax_synced_at',
    ];

    protected $casts = [
        'digitax_synced_at' => 'datetime',
        'is_offline' => 'boolean',
        'client_created_at' => 'datetime',
        'synced_at' => 'datetime',
        'stock_shortfall' => 'array',
    ];

    /*
    |-----------------------------------
    | Relationships
    |-----------------------------------
    */

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    public function getIsPaidAttribute()
    {
        return $this->balance <= 0;
    }
     // Items
    
    // Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Customer — withTrashed so sales still resolve deleted customers
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    // Cashier (User)
    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}