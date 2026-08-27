<?php

namespace App\Http\Controllers;

use App\Models\SalaryAdvance;
use App\Services\SalaryAdvanceService;
use Illuminate\Http\{JsonResponse, Request};

class SalaryAdvanceController extends Controller
{
    public function __construct(protected SalaryAdvanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SalaryAdvance::class);
        return response()->json($this->service->list(
            $request->integer('per_page', 50),
            $request->only(['employee_id', 'status'])
        ));
    }

    public function show(int $id): JsonResponse
    {
        $advance = $this->service->find($id);
        $this->authorize('view', $advance);
        return response()->json(['data' => $advance]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', SalaryAdvance::class);
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employee_profiles,id',
            'amount' => 'required|numeric|min:1',
            'repayment_amount_per_period' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $advance = $this->service->create($data);
        return response()->json(['message' => 'Salary advance requested.', 'data' => $advance], 201);
    }

    public function approve(int $id): JsonResponse
    {
        $advance = $this->service->find($id);
        $this->authorize('update', $advance);
        $advance = $this->service->approve($id);
        return response()->json(['message' => 'Salary advance approved.', 'data' => $advance]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $advance = $this->service->find($id);
        $this->authorize('update', $advance);
        $data = $request->validate(['notes' => 'nullable|string|max:255']);
        $advance = $this->service->reject($id, $data['notes'] ?? null);
        return response()->json(['message' => 'Salary advance rejected.', 'data' => $advance]);
    }

    public function cancel(int $id): JsonResponse
    {
        $advance = $this->service->find($id);
        $this->authorize('update', $advance);
        $advance = $this->service->cancel($id);
        return response()->json(['message' => 'Salary advance cancelled.', 'data' => $advance]);
    }

    public function destroy(int $id): JsonResponse
    {
        $advance = $this->service->find($id);
        $this->authorize('delete', $advance);
        $this->service->delete($id);
        return response()->json(['message' => 'Salary advance deleted.']);
    }
}
