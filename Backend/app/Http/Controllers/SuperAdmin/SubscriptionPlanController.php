<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function __construct(private SubscriptionPlanService $plans)
    {
    }

    // GET /superadmin/plans — full list (active + inactive) for management.
    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->plans->all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        $plan = $this->plans->create($validated);

        return response()->json(['message' => 'Plan created.', 'data' => $plan], 201);
    }

    public function update(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        $validated = $this->validated($request, $plan->id);

        $plan = $this->plans->update($plan, $validated);

        return response()->json(['message' => 'Plan updated.', 'data' => $plan]);
    }

    public function destroy(SubscriptionPlan $plan): JsonResponse
    {
        $this->plans->delete($plan);

        return response()->json(['message' => 'Plan deleted.']);
    }

    public function toggleActive(SubscriptionPlan $plan): JsonResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return response()->json(['message' => 'Plan updated.', 'data' => $plan]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:subscription_plans,id',
        ]);

        $this->plans->reorder($validated['ids']);

        return response()->json(['message' => 'Order saved.', 'data' => $this->plans->all()]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'           => 'required|string|max:100',
            'slug'           => 'nullable|string|max:100|alpha_dash',
            'billing_period' => 'required|in:trial,monthly,quarterly,annually',
            'price'          => 'required|numeric|min:0',
            'duration_days'  => 'required|integer|min:1|max:3650',
            'features'       => 'nullable|array',
            'features.*'     => 'nullable|string|max:150',
            'is_active'      => 'sometimes|boolean',
        ]);
    }
}
