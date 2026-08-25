<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Role $role): bool
    {
        return $user->business_id === $role->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Role $role): bool
    {
        return $user->business_id === $role->business_id;
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->business_id === $role->business_id;
    }
}
