<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTake extends Model
{
    protected $fillable = [
        'reference',
        'business_id',
        'user_id',
        'name',
        'description',
        'created_by'
    ];

    // 🔗 Relationships
public function items()
{
    return $this->hasMany(StockTakeItem::class, 'stock_take_id');
}


    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // StockTake.php


public function categories()
{
    return $this->belongsToMany(Category::class, 'stock_take_categories');
    // adjust pivot table name to match yours
}


}
