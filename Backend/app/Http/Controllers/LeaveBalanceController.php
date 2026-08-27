<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Services\LeaveBalanceService;
use Illuminate\Http\{JsonResponse, Request};

class LeaveBalanceController extends Controller
{
    public function __construct(protected LeaveBalanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LeaveBalance::class);
        $balances = $this->service->list($request->only(['employee_id', 'leave_type_id', 'year']));
        return response()->json(['data' => $balances]);
    }

    public function show(int $id): JsonResponse
    {
        $balance = $this->service->find($id);
        $this->authorize('view', $balance);
        return response()->json(['data' => $balance]);
    }

    public function adjust(Request $request, int $id): JsonResponse
    {
        $balance = $this->service->find($id);
        $this->authorize('update', $balance);
        $data = $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:500',
        ]);

        $balance = $this->service->adjust($id, (float) $data['amount'], $data['reason']);
        return response()->json(['message' => 'Balance adjusted.', 'data' => $balance]);
    }

    public function history(int $id): JsonResponse
    {
        $balance = $this->service->find($id);
        $this->authorize('view', $balance);
        return response()->json(['data' => $this->service->adjustmentHistory($id)]);
    }

    public function ensureForYear(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LeaveBalance::class);
        $year = $request->integer('year', now()->year);
        return response()->json(['data' => $this->service->ensureForYear($year)]);
    }
}
