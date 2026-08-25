<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'name',
        'abbreviation',
        'is_active',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
  public function branches()
    {
        return $this->belongsToMany(Branch::class, 'unit_branch');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
