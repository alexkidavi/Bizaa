<?php

namespace App\Http\Controllers;

use App\Services\LoyaltySettingService;
use Illuminate\Http\{JsonResponse, Request};

class LoyaltySettingController extends Controller
{
    public function __construct(protected LoyaltySettingService $service) {}

    public function show(): JsonResponse
    {
        $settings = $this->service->getOrCreate();
        $this->authorize('view', $settings);
        return response()->json(['data' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $settings = $this->service->getOrCreate();
        $this->authorize('update', $settings);
        $data = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'earn_amount_spent' => 'nullable|numeric|min:0.01',
            'earn_points_awarded' => 'nullable|integer|min:1',
            'point_value' => 'nullable|numeric|min:0.0001',
            'min_redeem_points' => 'nullable|integer|min:0',
        ]);

        $settings = $this->service->update($data);
        return response()->json(['message' => 'Loyalty settings updated.', 'data' => $settings]);
    }
}
