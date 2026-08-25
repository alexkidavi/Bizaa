<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitBranch extends Model
{
    use HasFactory;

    protected $table = 'unit_branch';

    protected $fillable = [
        'unit_id',
        'branch_id',
    ];

    /**
     * Brand relation
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'unit_branch');
    }
}