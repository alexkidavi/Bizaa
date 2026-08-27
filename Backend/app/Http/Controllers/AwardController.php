<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Services\AwardService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AwardController extends Controller
{
    public function __construct(protected AwardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Award::class);
        $awards = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['employee_id', 'award_type_id', 'status'])
        );
        return response()->json($awards);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Award::class);
        $data = $this->validated($request);

        $award = $this->service->create($data);
        return response()->json(['message' => 'Award recorded.', 'data' => $award], 201);
    }

    public function show(int $id): JsonResponse
    {
        $award = $this->service->find($id);
        $this->authorize('view', $award);
        return response()->json(['data' => $award]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $this->validated($request);

        $award = $this->service->update($id, $data);
        return response()->json(['message' => 'Award updated.', 'data' => $award]);
    }

    public function destroy(int $id): JsonResponse
    {
        $award = $this->service->find($id);
        $this->authorize('delete', $award);
        $this->service->delete($id);
        return response()->json(['message' => 'Award removed.']);
    }

    public function downloadDocument(int $id): StreamedResponse
    {
        $award = $this->service->find($id);
        $this->authorize('view', $award);

        $path = $this->service->documentPath($id);
        abort_if(!$path || !Storage::disk('local')->exists($path), 404, 'No document on this award.');

        return Storage::disk('local')->download($path);
    }

    protected function validated(Request $request): array
    {
        $businessId = $request->user()->business_id;

        return $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employee_profiles', 'id')->where('business_id', $businessId)],
            'award_type_id' => ['required', 'integer', Rule::exists('award_types', 'id')->where('business_id', $businessId)],
            'award_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'monetary_value' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'issued_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where('business_id', $businessId)],
            'status' => ['nullable', Rule::in(['issued', 'revoked'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'supporting_document' => ['nullable', 'file', 'max:5120'],
        ]);
    }
}
