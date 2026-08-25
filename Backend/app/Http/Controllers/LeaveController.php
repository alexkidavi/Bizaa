<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Services\LeaveService;
use Illuminate\Http\{JsonResponse, Request};

class LeaveController extends Controller
{
    public function __construct(protected LeaveService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Leave::class);
        $records = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['user_id', 'status', 'leave_type_id'])
        );
        return response()->json($records);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Leave::class);
        $data = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'days'          => 'nullable|integer|min:1',
            'reason'        => 'nullable|string|max:500',
        ]);

        $leave = $this->service->create($data);
        return response()->json([
            'message' => 'Leave request submitted.',
            'data'    => $leave->load(['user:id,name,email', 'leaveType:id,name']),
        ], 201);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $leave = $this->service->find($id);
        $this->authorize('update', $leave);
        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:255',
        ]);

        $leave = $this->service->updateStatus($id, $data['status'], $data['notes'] ?? null);
        return response()->json(['message' => "Leave {$data['status']}.", 'data' => $leave]);
    }

    public function destroy(int $id): JsonResponse
    {
        $leave = $this->service->find($id);
        $this->authorize('delete', $leave);
        $this->service->delete($id);
        return response()->json(['message' => 'Leave record deleted.']);
    }
}
