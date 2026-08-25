<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Branch;

class BranchPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Branch $branch)
    {
        return $branch->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Branch $branch)
    {
        return $branch->business_id === $user->business_id;
    }

    public function delete(User $user, Branch $branch)
    {
        return $branch->business_id === $user->business_id;
    }
}
