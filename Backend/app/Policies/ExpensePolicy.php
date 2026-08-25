<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Expense $expense)
    {
        return $expense->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Expense $expense)
    {
        return $expense->business_id === $user->business_id;
    }

    public function delete(User $user, Expense $expense)
    {
        return $expense->business_id === $user->business_id;
    }
}
