<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warning;

class WarningPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Warning $warning): bool
    {
        return $warning->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Warning $warning): bool
    {
        return $warning->business_id === $user->business_id;
    }

    public function delete(User $user, Warning $warning): bool
    {
        return $warning->business_id === $user->business_id;
    }
}
