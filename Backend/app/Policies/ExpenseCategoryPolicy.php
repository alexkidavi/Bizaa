<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ExpenseCategory;

class ExpenseCategoryPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, ExpenseCategory $expenseCategory)
    {
        return $expenseCategory->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, ExpenseCategory $expenseCategory)
    {
        return $expenseCategory->business_id === $user->business_id;
    }

    public function delete(User $user, ExpenseCategory $expenseCategory)
    {
        return $expenseCategory->business_id === $user->business_id;
    }
}
