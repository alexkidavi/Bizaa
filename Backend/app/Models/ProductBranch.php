<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBranch extends Model
{
    use HasFactory;

    protected $table = 'product_branch';

    protected $fillable = [
        'product_id',
        'branch_id',
        'stock',
        'buying_price',
        'selling_price',
        'is_listed',
    ];

    protected $casts = [
        'is_listed' => 'boolean',
        'stock' => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function increaseStock(float $qty): void
    {
        $this->stock += $qty;
        $this->save();
    }

    public function decreaseStock(float $qty): void
    {
        $this->stock = max(0, $this->stock - $qty);
        $this->save();
    }

    public function inStock(float $qty): bool
    {
        return $this->stock >= $qty;
    }
}