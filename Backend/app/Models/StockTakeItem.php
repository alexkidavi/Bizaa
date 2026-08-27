<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTakeItem extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'stock_take_id',
        'product_id',
        'branch_id',
        'recorded_stock',
        'counted_stock',
        'variance'
    ];

    protected $casts = [
        'recorded_stock' => 'decimal:4',
        'counted_stock' => 'decimal:4',
    ];

    // 🔗 Relationships
    public function stockTake()
    {
        return $this->belongsTo(StockTake::class);
    }

 
   public function branch()
{
    return $this->belongsTo(Branch::class, 'branch_id');
}
public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}
public function productBranch()
{
    return $this->belongsTo(ProductBranch::class, 'product_branch_id');
}
}