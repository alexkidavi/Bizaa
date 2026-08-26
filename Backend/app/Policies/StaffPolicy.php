<?php

namespace App\Policies;

use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, User $staff)
    {
        return $staff->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, User $staff)
    {
        return $staff->business_id === $user->business_id;
    }

    public function delete(User $user, User $staff)
    {
        return $staff->business_id === $user->business_id;
    }
}
