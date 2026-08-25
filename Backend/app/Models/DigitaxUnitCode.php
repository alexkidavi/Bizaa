<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitaxUnitCode extends Model
{
    protected $fillable = ['type', 'code', 'label'];

    public function scopePackage($query)
    {
        return $query->where('type', 'package');
    }

    public function scopeQuantity($query)
    {
        return $query->where('type', 'quantity');
    }
}
