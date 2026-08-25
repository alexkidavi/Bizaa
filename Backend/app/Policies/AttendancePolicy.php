<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;

class AttendancePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Attendance $attendance)
    {
        return $attendance->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Attendance $attendance)
    {
        return $attendance->business_id === $user->business_id;
    }

    public function delete(User $user, Attendance $attendance)
    {
        return $attendance->business_id === $user->business_id;
    }
}
