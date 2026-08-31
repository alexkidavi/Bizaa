<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminContact extends Model
{
    // Eloquent's default naming convention would derive `super_admin_contacts`
    // from this class name; explicit here to match the "superadmin" (no
    // underscore) spelling already used everywhere else in this app
    // (the `/superadmin` route prefix, `users.is_superadmin`).
    protected $table = 'superadmin_contacts';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
