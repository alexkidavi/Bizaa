<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeProfileRequest;
use App\Http\Requests\UpdateEmployeeProfileRequest;
use App\Models\EmployeeProfile;
use App\Services\EmployeeProfileService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeProfileController extends Controller
{
    public function __construct(protected EmployeeProfileService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EmployeeProfile::class);

        $employees = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['department_id', 'employment_status', 'search'])
        );

        return response()->json($employees);
    }

    public function store(StoreEmployeeProfileRequest $request): JsonResponse
    {
        $this->authorize('create', EmployeeProfile::class);

        $employee = $this->service->create($request->validated());
        $tempPassword = $employee->temp_password ?? null;

        return response()->json([
            'message' => 'Employee profile created.',
            'data' => $employee,
            // Only present when no password was supplied for a new login —
            // share this with the new staff member now, it won't be shown again.
            'temp_password' => $tempPassword,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $employee = $this->service->find($id);
        $this->authorize('view', $employee);

        return response()->json(['data' => $employee]);
    }

    public function update(UpdateEmployeeProfileRequest $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);

        $employee = $this->service->update($id, $request->validated());

        return response()->json(['message' => 'Employee profile updated.', 'data' => $employee]);
    }

    public function destroy(int $id): JsonResponse
    {
        $employee = $this->service->find($id);
        $this->authorize('delete', $employee);

        $this->service->delete($id);

        return response()->json(['message' => 'Employee profile removed.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $employee = $this->service->find($id);
        $this->authorize('update', $employee);

        $data = $request->validate([
            'employment_status' => ['required', Rule::in(['active', 'on_leave', 'suspended', 'resigned', 'terminated', 'retired'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $employee = $this->service->updateStatus($id, $data['employment_status'], $data['reason'] ?? null);

        return response()->json(['message' => 'Employment status updated.', 'data' => $employee]);
    }

    public function history(int $id): JsonResponse
    {
        $employee = $this->service->find($id);
        $this->authorize('view', $employee);

        return response()->json(['data' => $this->service->history($id)]);
    }

    public function removeDocument(Request $request, int $id): JsonResponse
    {
        $employee = $this->service->find($id);
        $this->authorize('update', $employee);

        $data = $request->validate(['path' => ['required', 'string']]);
        $employee = $this->service->removeDocument($id, $data['path']);

        return response()->json(['message' => 'Document removed.', 'data' => $employee]);
    }

    public function downloadDocument(Request $request, int $id): StreamedResponse
    {
        $employee = $this->service->find($id);
        $this->authorize('view', $employee);

        $data = $request->validate(['path' => ['required', 'string']]);
        $path = $this->service->documentPath($id, $data['path']);

        return Storage::disk('local')->download($path);
    }
}
