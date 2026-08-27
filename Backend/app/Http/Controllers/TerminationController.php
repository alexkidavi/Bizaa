<?php

namespace App\Http\Controllers;

use App\Models\Termination;
use App\Services\TerminationService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TerminationController extends Controller
{
    public function __construct(protected TerminationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Termination::class);
        $terminations = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['employee_id', 'status'])
        );
        return response()->json($terminations);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Termination::class);
        $data = $this->validated($request);

        $termination = $this->service->create($data);
        return response()->json(['message' => 'Termination recorded.', 'data' => $termination], 201);
    }

    public function show(int $id): JsonResponse
    {
        $termination = $this->service->find($id);
        $this->authorize('view', $termination);
        return response()->json(['data' => $termination]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $this->validated($request);

        $termination = $this->service->update($id, $data);
        return response()->json(['message' => 'Termination updated.', 'data' => $termination]);
    }

    public function destroy(int $id): JsonResponse
    {
        $termination = $this->service->find($id);
        $this->authorize('delete', $termination);
        $this->service->delete($id);
        return response()->json(['message' => 'Termination removed.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'completed', 'cancelled'])],
            'hr_comments' => ['nullable', 'string', 'max:2000'],
            'manager_comments' => ['nullable', 'string', 'max:2000'],
            'final_settlement_amount' => ['nullable', 'numeric', 'min:0'],
            'final_settlement_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $termination = $this->service->updateStatus($id, $data['status'], $data);
        return response()->json(['message' => 'Termination status updated.', 'data' => $termination]);
    }

    public function downloadDocument(Request $request, int $id): StreamedResponse
    {
        $termination = $this->service->find($id);
        $this->authorize('view', $termination);

        $data = $request->validate(['path' => ['required', 'string']]);
        $path = $this->service->documentPath($id, $data['path']);

        return Storage::disk('local')->download($path);
    }

    protected function validated(Request $request): array
    {
        $businessId = $request->user()->business_id;

        return $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employee_profiles', 'id')->where('business_id', $businessId)],
            'termination_type_id' => ['required', 'integer', Rule::exists('termination_types', 'id')->where('business_id', $businessId)],
            'termination_date' => ['required', 'date'],
            'effective_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'detailed_explanation' => ['nullable', 'string', 'max:2000'],
            'notice_given' => ['nullable', 'boolean'],
            'notice_period_days' => ['nullable', 'integer', 'min:0'],
            'hr_comments' => ['nullable', 'string', 'max:2000'],
            'manager_comments' => ['nullable', 'string', 'max:2000'],
            'final_settlement_amount' => ['nullable', 'numeric', 'min:0'],
            'final_settlement_notes' => ['nullable', 'string', 'max:2000'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'max:5120'],
        ]);
    }
}
