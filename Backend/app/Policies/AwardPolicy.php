<?php

namespace App\Policies;

use App\Models\Award;
use App\Models\User;

class AwardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Award $award): bool
    {
        return $award->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Award $award): bool
    {
        return $award->business_id === $user->business_id;
    }

    public function delete(User $user, Award $award): bool
    {
        return $award->business_id === $user->business_id;
    }
}
