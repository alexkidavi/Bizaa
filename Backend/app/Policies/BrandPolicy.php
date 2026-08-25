<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Brand;

class BrandPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Brand $brand)
    {
        return $brand->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Brand $brand)
    {
        return $brand->business_id === $user->business_id;
    }

    public function delete(User $user, Brand $brand)
    {
        return $brand->business_id === $user->business_id;
    }
}
