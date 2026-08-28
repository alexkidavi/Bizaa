<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'name',
        'email',
        'phone',
        'status',
        'notes',
        'loyalty_points_balance',
    ];

    protected $casts = [
        'loyalty_points_balance' => 'integer',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function crmContact()
    {
        return $this->hasOne(CrmContact::class, 'customer_id');
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyPointTransaction::class);
    }
}