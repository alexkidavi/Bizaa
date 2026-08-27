<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Services\LeaveService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    public function __construct(protected LeaveService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Leave::class);
        $records = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['employee_id', 'status', 'leave_type_id'])
        );
        return response()->json($records);
    }

    public function show(int $id): JsonResponse
    {
        $leave = $this->service->find($id);
        $this->authorize('view', $leave);
        return response()->json(['data' => $leave]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Leave::class);
        $data = $request->validate([
            'employee_id'             => 'required|integer|exists:employee_profiles,id',
            'leave_type_id'           => 'required|integer|exists:leave_types,id',
            'start_date'              => 'required|date',
            'end_date'                => 'required|date|after_or_equal:start_date',
            'working_days'            => 'nullable|integer|min:1',
            'reason'                  => 'nullable|string|max:500',
            'status'                  => 'nullable|in:draft,submitted',
            'supporting_documents'    => 'nullable|array',
            'supporting_documents.*'  => 'file|max:5120',
        ]);

        $leave = $this->service->create($data);
        return response()->json(['message' => 'Leave request submitted.', 'data' => $leave], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $leave = $this->service->find($id);
        $this->authorize('update', $leave);
        $data = $request->validate([
            'leave_type_id'           => 'nullable|integer|exists:leave_types,id',
            'start_date'              => 'nullable|date',
            'end_date'                => 'nullable|date|after_or_equal:start_date',
            'working_days'            => 'nullable|integer|min:1',
            'reason'                  => 'nullable|string|max:500',
            'supporting_documents'    => 'nullable|array',
            'supporting_documents.*'  => 'file|max:5120',
        ]);

        $leave = $this->service->update($id, $data);
        return response()->json(['message' => 'Leave request updated.', 'data' => $leave]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $leave = $this->service->find($id);
        $this->authorize('update', $leave);
        $data = $request->validate([
            'status' => 'required|in:submitted,approved,rejected,cancelled,completed',
            'notes'  => 'nullable|string|max:255',
        ]);

        $leave = $this->service->updateStatus($id, $data['status'], $data['notes'] ?? null);
        return response()->json(['message' => "Leave {$data['status']}.", 'data' => $leave]);
    }

    public function downloadDocument(Request $request, int $id): mixed
    {
        $leave = $this->service->find($id);
        $this->authorize('view', $leave);
        $path = $this->service->documentPath($id, $request->query('path', ''));
        abort_unless(Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path);
    }

    public function destroy(int $id): JsonResponse
    {
        $leave = $this->service->find($id);
        $this->authorize('delete', $leave);
        $this->service->delete($id);
        return response()->json(['message' => 'Leave record deleted.']);
    }
}
