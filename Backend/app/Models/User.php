<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login_pin',
        'phone',
        'role_id',
        'department_id',
        'shift_id',
        'basic_salary',
        'business_id',
        'registered_by',
        'is_active',
        // 'is_superadmin',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'login_pin',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'is_superadmin'     => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

  

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function registeredStaff()
    {
        return $this->hasMany(User::class, 'registered_by');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }


    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeStaffOnly($query)
    {
        return $query->whereNotNull('registered_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | JWT Implementation
    |--------------------------------------------------------------------------
    */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasPermission(string $permissionName): bool
{
    // Admin override
    if ($this->role?->name === 'admin') {
        return true;
    }

    return $this->role?->permissions()
        ->where('name', $permissionName)
        ->exists();
}

public function permissions()
{
    return $this->role?->permissions();
}

public function getAllPermissionsList()
{
    if ($this->role?->name === 'admin') {
        return Permission::pluck('name');
    }

    return collect([]);
}
public function role()
{
    return $this->belongsTo(Role::class);
}
}