<?php

namespace App\Http\Controllers;

use App\Models\Resignation;
use App\Services\ResignationService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResignationController extends Controller
{
    public function __construct(protected ResignationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Resignation::class);
        $resignations = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['employee_id', 'status'])
        );
        return response()->json($resignations);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Resignation::class);
        $data = $this->validated($request);

        $resignation = $this->service->create($data);
        return response()->json(['message' => 'Resignation submitted.', 'data' => $resignation], 201);
    }

    public function show(int $id): JsonResponse
    {
        $resignation = $this->service->find($id);
        $this->authorize('view', $resignation);
        return response()->json(['data' => $resignation]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $this->validated($request);

        $resignation = $this->service->update($id, $data);
        return response()->json(['message' => 'Resignation updated.', 'data' => $resignation]);
    }

    public function destroy(int $id): JsonResponse
    {
        $resignation = $this->service->find($id);
        $this->authorize('delete', $resignation);
        $this->service->delete($id);
        return response()->json(['message' => 'Resignation removed.']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);

        $data = $request->validate([
            'status' => ['required', Rule::in(['submitted', 'under_review', 'approved', 'rejected', 'completed', 'cancelled'])],
            'hr_comments' => ['nullable', 'string', 'max:2000'],
            'manager_comments' => ['nullable', 'string', 'max:2000'],
            'actual_last_working_day' => ['nullable', 'date'],
        ]);

        $resignation = $this->service->updateStatus($id, $data['status'], $data);
        return response()->json(['message' => 'Resignation status updated.', 'data' => $resignation]);
    }

    public function downloadDocument(Request $request, int $id): StreamedResponse
    {
        $resignation = $this->service->find($id);
        $this->authorize('view', $resignation);

        $data = $request->validate(['path' => ['required', 'string']]);
        $path = $this->service->documentPath($id, $data['path']);

        return Storage::disk('local')->download($path);
    }

    protected function validated(Request $request): array
    {
        $businessId = $request->user()->business_id;

        return $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employee_profiles', 'id')->where('business_id', $businessId)],
            'resignation_date' => ['required', 'date'],
            'notice_date' => ['nullable', 'date'],
            'notice_period_days' => ['nullable', 'integer', 'min:0'],
            'expected_last_working_day' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'employee_comments' => ['nullable', 'string', 'max:2000'],
            'hr_comments' => ['nullable', 'string', 'max:2000'],
            'manager_comments' => ['nullable', 'string', 'max:2000'],
            'exit_interview_notes' => ['nullable', 'string', 'max:2000'],
            'exit_interview_conducted_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where('business_id', $businessId)],
            'exit_interview_date' => ['nullable', 'date'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'max:5120'],
        ]);
    }
}
