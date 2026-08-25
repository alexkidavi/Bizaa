<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Product $product)
    {
        return $user->business_id === $product->business_id;
    }

    public function create(User $user)
    {
      return $user->business_id !== null;
    }

    public function update(User $user, Product $product)
    {
        return $user->business_id === $product->business_id;
    }

    public function delete(User $user, Product $product)
    {
        return $user->business_id === $product->business_id;
    }
}
