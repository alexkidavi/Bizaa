<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBranch extends Model
{
    use HasFactory;

    protected $table = 'supplier_branch';

    protected $fillable = [
        'supplier_id',
        'branch_id',
    ];

    /**
     * Brand relation
     */
    public function category()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Branch relation
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}