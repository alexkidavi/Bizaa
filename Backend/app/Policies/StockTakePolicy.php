<?php

namespace App\Policies;

use App\Models\StockTake;
use App\Models\User;

class StockTakePolicy
{
    public function view(User $user, StockTake $stockTake): bool
    {
        return $user->business_id === $stockTake->business_id;
    }

    public function update(User $user, StockTake $stockTake): bool
    {
        return $user->business_id === $stockTake->business_id;
    }

    public function delete(User $user, StockTake $stockTake): bool
    {
        return $user->business_id === $stockTake->business_id;
    }
}
