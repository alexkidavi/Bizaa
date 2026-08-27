<?php

namespace App\Http\Controllers;

use App\Models\PerformanceRating;
use App\Services\PerformanceRatingService;
use Illuminate\Http\{JsonResponse, Request};

class PerformanceRatingController extends Controller
{
    public function __construct(protected PerformanceRatingService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', PerformanceRating::class);
        return response()->json(['data' => $this->service->list((bool) request('active_only'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PerformanceRating::class);
        $data = $this->validated($request);

        $rating = $this->service->create($data);
        return response()->json(['message' => 'Performance rating created.', 'data' => $rating], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $this->validated($request);

        $rating = $this->service->update($id, $data);
        return response()->json(['message' => 'Performance rating updated.', 'data' => $rating]);
    }

    public function destroy(int $id): JsonResponse
    {
        $rating = $this->service->find($id);
        $this->authorize('delete', $rating);
        $this->service->delete($id);
        return response()->json(['message' => 'Performance rating removed.']);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'score_value' => 'nullable|numeric|min:0|max:99.9',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
