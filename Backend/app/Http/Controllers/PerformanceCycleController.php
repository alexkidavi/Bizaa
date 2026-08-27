<?php

namespace App\Http\Controllers;

use App\Models\PerformanceCycle;
use App\Services\PerformanceCycleService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class PerformanceCycleController extends Controller
{
    public function __construct(protected PerformanceCycleService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', PerformanceCycle::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PerformanceCycle::class);
        $data = $this->validated($request);

        $cycle = $this->service->create($data);
        return response()->json(['message' => 'Performance cycle created.', 'data' => $cycle], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $this->validated($request);

        $cycle = $this->service->update($id, $data);
        return response()->json(['message' => 'Performance cycle updated.', 'data' => $cycle]);
    }

    public function destroy(int $id): JsonResponse
    {
        $cycle = $this->service->find($id);
        $this->authorize('delete', $cycle);
        $this->service->delete($id);
        return response()->json(['message' => 'Performance cycle removed.']);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'type' => ['nullable', Rule::in(['monthly', 'quarterly', 'biannual', 'annual', 'custom'])],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['nullable', Rule::in(['draft', 'active', 'closed'])],
        ]);
    }
}
