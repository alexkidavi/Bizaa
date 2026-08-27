<?php

namespace App\Policies;

use App\Models\PerformanceReview;
use App\Models\User;

class PerformanceReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, PerformanceReview $review): bool
    {
        return $review->business_id === $user->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, PerformanceReview $review): bool
    {
        return $review->business_id === $user->business_id;
    }

    public function delete(User $user, PerformanceReview $review): bool
    {
        return $review->business_id === $user->business_id;
    }
}
