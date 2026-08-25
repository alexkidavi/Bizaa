<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Leave;

class LeavePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Leave $leave)
    {
        return $leave->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Leave $leave)
    {
        return $leave->business_id === $user->business_id;
    }

    public function delete(User $user, Leave $leave)
    {
        return $leave->business_id === $user->business_id;
    }
}
