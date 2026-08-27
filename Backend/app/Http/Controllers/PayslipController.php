<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\PayslipService;
use App\Services\ReportBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;

class PayslipController extends Controller
{
    public function __construct(protected PayslipService $service) {}

    public function show(int $id): JsonResponse
    {
        $payslip = $this->service->find($id);
        $this->authorize('view', $payslip);
        return response()->json(['data' => $payslip]);
    }

    public function pdf(int $id): mixed
    {
        $payslip = $this->service->find($id);
        $this->authorize('view', $payslip);

        $branding = ReportBranding::forBusiness(Business::find($payslip->business_id));

        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payslip' => $payslip,
            'employee' => $payslip->employee,
            'period' => $payslip->payrollPeriod,
            'branding' => $branding,
        ]);

        $fileName = 'payslip-' . str_replace(' ', '-', $payslip->payrollPeriod->name) . '-' . ($payslip->employee->employee_number ?? $payslip->employee_id) . '.pdf';

        return $pdf->download($fileName);
    }
}
