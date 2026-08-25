<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'product_code',
        'business_id',
        'user_id',
        'barcode',
        'parent_id',
        'category_id',
        'brand_id',
        'unit_id',
        'buying_price',
        'selling_price',
        'wholesale_price',
        'tax_type',
        'opening_stock',
        'reorder_level',
        'current_stock',
        'for_sale',
        'for_purchase',
        'color',
        'image',
        'description',
        'short_description',
        'weight',
        'tags',

        'item_class_code',
        'item_type_code',
        'tax_type_code',
        'package_unit_code',
        'quantity_unit_code',
        'origin_nation_code',
        'digitax_item_id',
        'etims_item_code',
        'digitax_status',
        'digitax_synced_at',
        'digitax_error',
    ];

    protected $casts = [
        'tags' => 'array',
        'digitax_synced_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Parent product (main product)
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // Variants (child products)
    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    // Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
// Branches where product exists
public function branches()
{
    return $this->belongsToMany(Branch::class, 'product_branch')
        ->withPivot(['stock', 'selling_price', 'buying_price'])
        ->withTimestamps();
}
    // Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function isVariant(): bool
    {
        return !is_null($this->parent_id);
    }

    public function isMainProduct(): bool
    {
        return is_null($this->parent_id);
    }
}