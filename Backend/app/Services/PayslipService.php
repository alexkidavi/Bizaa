<?php

namespace App\Services;

use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;

class PayslipService
{
    public function find(int $id): Payslip
    {
        return Payslip::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(['employee', 'payrollPeriod', 'lines'])
            ->firstOrFail();
    }
}
