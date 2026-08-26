<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryBranch extends Model
{
    use HasFactory;

    protected $table = 'category_branch';

    protected $fillable = [
        'category_id',
        'branch_id',
    ];

    /**
     * Brand relation
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Branch relation
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}