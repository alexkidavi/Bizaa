<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payroll;

class PayrollPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Payroll $payroll)
    {
        return $payroll->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Payroll $payroll)
    {
        return $payroll->business_id === $user->business_id;
    }

    public function delete(User $user, Payroll $payroll)
    {
        return $payroll->business_id === $user->business_id;
    }
}
