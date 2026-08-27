<?php

namespace App\Policies;

use App\Models\Bank;
use App\Models\User;

class BankPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Bank $bank): bool
    {
        return $bank->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Bank $bank): bool
    {
        return $bank->business_id === $user->business_id;
    }

    public function delete(User $user, Bank $bank): bool
    {
        return $bank->business_id === $user->business_id;
    }
}
