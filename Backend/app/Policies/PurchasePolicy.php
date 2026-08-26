<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Purchase $purchase)
    {
        return $purchase->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Purchase $purchase)
    {
        return $purchase->business_id === $user->business_id;
    }

    public function delete(User $user, Purchase $purchase)
    {
        return $purchase->business_id === $user->business_id;
    }
}
