<?php

namespace App\Policies;

use App\Models\Termination;
use App\Models\User;

class TerminationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Termination $termination): bool
    {
        return $termination->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Termination $termination): bool
    {
        return $termination->business_id === $user->business_id;
    }

    public function delete(User $user, Termination $termination): bool
    {
        return $termination->business_id === $user->business_id;
    }
}
