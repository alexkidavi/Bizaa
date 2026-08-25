<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Overtime;

class OvertimePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Overtime $overtime)
    {
        return $overtime->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Overtime $overtime)
    {
        return $overtime->business_id === $user->business_id;
    }

    public function delete(User $user, Overtime $overtime)
    {
        return $overtime->business_id === $user->business_id;
    }
}
