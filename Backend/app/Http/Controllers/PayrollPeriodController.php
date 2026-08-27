<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Services\PayrollPeriodService;
use Illuminate\Http\{JsonResponse, Request};

class PayrollPeriodController extends Controller
{
    public function __construct(protected PayrollPeriodService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PayrollPeriod::class);
        return response()->json($this->service->list($request->integer('per_page', 50)));
    }

    public function show(int $id): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('view', $period);
        return response()->json(['data' => $this->service->findWithPayslips($id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PayrollPeriod::class);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pay_date' => 'nullable|date',
        ]);

        $period = $this->service->create($data);
        return response()->json(['message' => 'Payroll period created.', 'data' => $period], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('delete', $period);
        $this->service->delete($id);
        return response()->json(['message' => 'Payroll period deleted.']);
    }

    public function calculate(int $id): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('update', $period);
        $period = $this->service->calculate($id);
        return response()->json(['message' => 'Payroll calculated.', 'data' => $period]);
    }

    public function cancel(int $id): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('update', $period);
        $period = $this->service->cancel($id);
        return response()->json(['message' => 'Payroll period cancelled.', 'data' => $period]);
    }

    public function approve(int $id): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('update', $period);
        $period = $this->service->approve($id);
        return response()->json(['message' => 'Payroll approved.', 'data' => $period]);
    }

    public function finalize(int $id): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('update', $period);
        $period = $this->service->finalize($id);
        return response()->json(['message' => 'Payroll finalized.', 'data' => $period]);
    }

    public function markPaid(int $id): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('update', $period);
        $period = $this->service->markPaid($id);
        return response()->json(['message' => 'Payroll marked as paid.', 'data' => $period]);
    }

    public function toggleExclude(Request $request, int $id, int $payslipId): JsonResponse
    {
        $period = $this->service->find($id);
        $this->authorize('update', $period);
        $data = $request->validate(['excluded' => 'required|boolean']);
        $payslip = $this->service->toggleExclude($id, $payslipId, $data['excluded']);
        return response()->json(['message' => $data['excluded'] ? 'Employee excluded from this payroll run.' : 'Employee re-included.', 'data' => $payslip]);
    }
}
