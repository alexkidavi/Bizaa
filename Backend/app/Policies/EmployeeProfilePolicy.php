<?php

namespace App\Policies;

use App\Models\EmployeeProfile;
use App\Models\User;

class EmployeeProfilePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, EmployeeProfile $employee)
    {
        return $employee->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, EmployeeProfile $employee)
    {
        return $employee->business_id === $user->business_id;
    }

    public function delete(User $user, EmployeeProfile $employee)
    {
        return $employee->business_id === $user->business_id;
    }
}
