<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * One shared policy for AwardType, WarningType, and TerminationType —
 * the ownership check is identical for all three, so it's registered
 * against each model class rather than duplicated per-model.
 */
class HrmLookupTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Model $type): bool
    {
        return $type->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Model $type): bool
    {
        return $type->business_id === $user->business_id;
    }

    public function delete(User $user, Model $type): bool
    {
        return $type->business_id === $user->business_id;
    }
}
