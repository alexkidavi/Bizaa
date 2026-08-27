<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Services\PerformanceReviewService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class PerformanceReviewController extends Controller
{
    public function __construct(protected PerformanceReviewService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PerformanceReview::class);
        $reviews = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['employee_id', 'performance_cycle_id', 'status'])
        );
        return response()->json($reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PerformanceReview::class);
        $data = $this->validated($request);

        $review = $this->service->create($data);
        return response()->json(['message' => 'Performance review created.', 'data' => $review], 201);
    }

    public function show(int $id): JsonResponse
    {
        $review = $this->service->find($id);
        $this->authorize('view', $review);
        return response()->json(['data' => $review]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $this->validated($request);

        $review = $this->service->update($id, $data);
        return response()->json(['message' => 'Performance review updated.', 'data' => $review]);
    }

    public function destroy(int $id): JsonResponse
    {
        $review = $this->service->find($id);
        $this->authorize('delete', $review);
        $this->service->delete($id);
        return response()->json(['message' => 'Performance review removed.']);
    }

    protected function validated(Request $request): array
    {
        $businessId = $request->user()->business_id;

        return $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employee_profiles', 'id')->where('business_id', $businessId)],
            'performance_cycle_id' => ['required', 'integer', Rule::exists('performance_cycles', 'id')->where('business_id', $businessId)],
            'reviewer_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('business_id', $businessId)],
            'review_date' => ['required', 'date'],
            'goals' => ['nullable', 'string', 'max:3000'],
            'kpis' => ['nullable', 'string', 'max:3000'],
            'competencies' => ['nullable', 'string', 'max:3000'],
            'self_assessment' => ['nullable', 'string', 'max:3000'],
            'manager_assessment' => ['nullable', 'string', 'max:3000'],
            'overall_score' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'performance_rating_id' => ['nullable', 'integer', Rule::exists('performance_ratings', 'id')->where('business_id', $businessId)],
            'strengths' => ['nullable', 'string', 'max:2000'],
            'areas_for_improvement' => ['nullable', 'string', 'max:2000'],
            'development_plan' => ['nullable', 'string', 'max:2000'],
            'comments' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::in(['draft', 'submitted', 'acknowledged', 'completed'])],
        ]);
    }
}
