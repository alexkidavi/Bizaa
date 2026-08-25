<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\{JsonResponse, Request};

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payroll::class);
        $records = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['month', 'year', 'status'])
        );
        return response()->json($records);
    }

    public function generate(Request $request): JsonResponse
    {
        $this->authorize('create', Payroll::class);
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2020',
        ]);

        $created = $this->service->generate($data['month'], $data['year']);
        return response()->json([
            'message' => count($created) . ' payroll records generated.',
            'data'    => $created,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payroll = $this->service->find($id);
        $this->authorize('update', $payroll);
        $data = $request->validate([
            'basic_salary' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'allowances'   => 'nullable|numeric|min:0',
            'deductions'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:255',
        ]);

        $payroll = $this->service->update($id, $data);
        return response()->json(['message' => 'Payroll updated.', 'data' => $payroll]);
    }

    public function markPaid(int $id): JsonResponse
    {
        $payroll = $this->service->find($id);
        $this->authorize('update', $payroll);
        $payroll = $this->service->markPaid($id);
        return response()->json(['message' => 'Payroll marked as paid.', 'data' => $payroll]);
    }

    public function destroy(int $id): JsonResponse
    {
        $payroll = $this->service->find($id);
        $this->authorize('delete', $payroll);
        $this->service->delete($id);
        return response()->json(['message' => 'Payroll record deleted.']);
    }
}
