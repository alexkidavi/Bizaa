<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Quotation $quotation)
    {
        return $quotation->business_id === $user->business_id;
    }

    public function create(User $user)
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Quotation $quotation)
    {
        return $quotation->business_id === $user->business_id;
    }

    public function delete(User $user, Quotation $quotation)
    {
        return $quotation->business_id === $user->business_id;
    }
}
