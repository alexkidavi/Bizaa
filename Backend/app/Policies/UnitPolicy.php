<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Unit;

class UnitPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Unit $Unit)
    {
        return $Unit->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Unit $Unit)
    {
        return $Unit->business_id === $user->business_id;
    }

    public function delete(User $user, Unit $Unit)
    {
        return $Unit->business_id === $user->business_id;
    }
}
