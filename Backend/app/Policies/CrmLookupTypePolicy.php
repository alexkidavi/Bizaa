<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared policy for CRM entities whose authorization is purely
 * business-id ownership — CrmCompany, CrmContact, CrmTag. Identical shape
 * to HrmLookupTypePolicy; kept as a separate class for naming clarity in
 * the policy list rather than reusing the HR one for a CRM resource.
 */
class CrmLookupTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Model $model): bool
    {
        return $model->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Model $model): bool
    {
        return $model->business_id === $user->business_id;
    }

    public function delete(User $user, Model $model): bool
    {
        return $model->business_id === $user->business_id;
    }
}
