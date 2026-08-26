<?php

namespace App\Policies;

use App\Models\BusinessSubscription;
use App\Models\User;

class BusinessSubscriptionPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, BusinessSubscription $subscription)
    {
        return $subscription->business_id === $user->business_id;
    }
}
