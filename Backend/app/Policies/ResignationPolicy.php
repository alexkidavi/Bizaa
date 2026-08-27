<?php

namespace App\Policies;

use App\Models\Resignation;
use App\Models\User;

class ResignationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Resignation $resignation): bool
    {
        return $resignation->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Resignation $resignation): bool
    {
        return $resignation->business_id === $user->business_id;
    }

    public function delete(User $user, Resignation $resignation): bool
    {
        return $resignation->business_id === $user->business_id;
    }
}
