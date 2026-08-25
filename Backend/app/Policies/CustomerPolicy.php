<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;

class CustomerPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Customer $customer)
    {
        return $customer->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Customer $customer)
    {
        return $customer->business_id === $user->business_id;
    }

    public function delete(User $user, Customer $customer)
    {
        return $customer->business_id === $user->business_id;
    }
}
