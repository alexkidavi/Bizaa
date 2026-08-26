<?php

namespace App\Policies;

use App\Models\EcommerceStore;
use App\Models\User;

class EcommerceStorePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, EcommerceStore $store)
    {
        return $store->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, EcommerceStore $store)
    {
        return $store->business_id === $user->business_id;
    }

    public function delete(User $user, EcommerceStore $store)
    {
        return $store->business_id === $user->business_id;
    }
}
