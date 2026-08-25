<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shift;

class ShiftPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Shift $shift)
    {
        return $shift->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Shift $shift)
    {
        return $shift->business_id === $user->business_id;
    }

    public function delete(User $user, Shift $shift)
    {
        return $shift->business_id === $user->business_id;
    }
}
