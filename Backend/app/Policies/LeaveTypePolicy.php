<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LeaveType;

class LeaveTypePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, LeaveType $leaveType)
    {
        return $leaveType->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, LeaveType $leaveType)
    {
        return $leaveType->business_id === $user->business_id;
    }

    public function delete(User $user, LeaveType $leaveType)
    {
        return $leaveType->business_id === $user->business_id;
    }
}
