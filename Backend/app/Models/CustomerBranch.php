<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBranch extends Model
{
    use HasFactory;

    protected $table = 'customer_branch';

    protected $fillable = [
        'customer_id',
        'branch_id',
    ];

    /**
     * Customer relation
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Branch relation
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}