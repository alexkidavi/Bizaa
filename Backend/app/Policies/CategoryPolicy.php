<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Category $Category)
    {
        return $Category->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Category $Category)
    {
        return $Category->business_id === $user->business_id;
    }

    public function delete(User $user, Category $Category)
    {
        return $Category->business_id === $user->business_id;
    }
}
