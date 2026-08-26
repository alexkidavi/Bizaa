<?php

namespace App\Policies;

use App\Models\MpesaPayment;
use App\Models\User;

class MpesaPaymentPolicy
{
    public function viewAny(User $user)
    {
        return $user->business_id !== null;
    }

    public function view(User $user, MpesaPayment $payment)
    {
        return $payment->business_id === $user->business_id;
    }
}
