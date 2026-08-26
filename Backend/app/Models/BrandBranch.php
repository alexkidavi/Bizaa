<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandBranch extends Model
{
    use HasFactory;

    protected $table = 'brand_branch';

    protected $fillable = [
        'brand_id',
        'branch_id',
    ];

    /**
     * Brand relation
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Branch relation
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}