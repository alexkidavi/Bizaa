<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\{JsonResponse, Request};

class LeaveTypeController extends Controller
{
    public function __construct(protected LeaveTypeService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', LeaveType::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LeaveType::class);
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'days_allowed' => 'required|integer|min:0',
            'description'  => 'nullable|string|max:255',
        ]);

        $lt = $this->service->create($data);
        return response()->json(['message' => 'Leave type created.', 'data' => $lt], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $lt = $this->service->find($id);
        $this->authorize('update', $lt);
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'days_allowed' => 'required|integer|min:0',
            'description'  => 'nullable|string|max:255',
        ]);

        $lt = $this->service->update($id, $data);
        return response()->json(['message' => 'Leave type updated.', 'data' => $lt]);
    }

    public function destroy(int $id): JsonResponse
    {
        $lt = $this->service->find($id);
        $this->authorize('delete', $lt);
        $this->service->delete($id);
        return response()->json(['message' => 'Leave type deleted.']);
    }
}
