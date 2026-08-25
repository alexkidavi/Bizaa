<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceStore extends Model
{
    use SoftDeletes;

    protected $table = 'ecommerce_stores';

    protected $fillable = [
        'business_id',
        'branch_id',
        'user_id',
        'name',
        'slug',
        'tagline',
        'description',
        'category',
        'banner_color',
        'phone',
        'email',
        'hours',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
