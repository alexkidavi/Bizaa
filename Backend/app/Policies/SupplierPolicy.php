<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Supplier;

class SupplierPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Supplier $supplier)
    {
        return $supplier->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Supplier $supplier)
    {
        return $supplier->business_id === $user->business_id;
    }

    public function delete(User $user, Supplier $supplier)
    {
        return $supplier->business_id === $user->business_id;
    }
}
