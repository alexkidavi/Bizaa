<?php

namespace App\Http\Controllers;

use App\Models\Warning;
use App\Services\WarningService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarningController extends Controller
{
    public function __construct(protected WarningService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Warning::class);
        $warnings = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['employee_id', 'status', 'severity'])
        );
        return response()->json($warnings);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Warning::class);
        $data = $this->validated($request);

        $warning = $this->service->create($data);
        return response()->json(['message' => 'Warning recorded.', 'data' => $warning], 201);
    }

    public function show(int $id): JsonResponse
    {
        $warning = $this->service->find($id);
        $this->authorize('view', $warning);
        return response()->json(['data' => $warning]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $this->validated($request);

        $warning = $this->service->update($id, $data);
        return response()->json(['message' => 'Warning updated.', 'data' => $warning]);
    }

    public function destroy(int $id): JsonResponse
    {
        $warning = $this->service->find($id);
        $this->authorize('delete', $warning);
        $this->service->delete($id);
        return response()->json(['message' => 'Warning removed.']);
    }

    public function downloadDocument(Request $request, int $id): StreamedResponse
    {
        $warning = $this->service->find($id);
        $this->authorize('view', $warning);

        $data = $request->validate(['path' => ['required', 'string']]);
        $path = $this->service->documentPath($id, $data['path']);

        return Storage::disk('local')->download($path);
    }

    protected function validated(Request $request): array
    {
        $businessId = $request->user()->business_id;

        return $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employee_profiles', 'id')->where('business_id', $businessId)],
            'warning_type_id' => ['required', 'integer', Rule::exists('warning_types', 'id')->where('business_id', $businessId)],
            'warning_date' => ['required', 'date'],
            'severity' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'issued_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where('business_id', $businessId)],
            'employee_response' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['active', 'acknowledged', 'resolved'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'max:5120'],
        ]);
    }
}
