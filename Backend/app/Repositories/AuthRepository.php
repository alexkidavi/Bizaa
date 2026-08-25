<?php

namespace App\Repositories;

use App\Models\Branch;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Str;

class AuthRepository implements AuthRepositoryInterface
{
    public function createBusiness(array $data): Business
    {
        $business = Business::create([
            'business_name'     => $data['business_name'],
            'business_email'    => $data['business_email'],
            'business_phone'    => $data['business_phone'],
            'business_website'  => $data['business_website'] ?? null,
            'business_industry' => $data['business_industry'],
            'business_location' => $data['business_location'],
            'business_code'     => 'TMP',
        ]);

        $business->business_code = 'BUS' . str_pad((string) $business->id, 4, '0', STR_PAD_LEFT);
        $business->save();

        return $business;
    }

    public function createAdminRoleWithAllPermissions(int $businessId): Role
    {
        $role = Role::create([
            'name'        => 'Admin',
            'business_id' => $businessId,
            'guard_name'  => 'api',
        ]);

        $permissionIds = Permission::where('name', 'not like', 'superadmin.%')->pluck('id');
        $role->permissions()->sync($permissionIds);

        return $role;
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function createDefaultBranch(int $businessId, int $userId): void
    {
        Branch::create([
            'name'        => 'Default Branch',
            'business_id' => $businessId,
            'user_id'     => $userId,
            'is_active'   => 1,
        ]);
    }

    public function startTrialSubscription(int $businessId): void
    {
        BusinessSubscription::createTrial($businessId);
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }
}
