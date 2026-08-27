<?php

namespace App\Http\Controllers;

use App\Services\PayrollSettingService;
use Illuminate\Http\{JsonResponse, Request};

class PayrollSettingController extends Controller
{
    public function __construct(protected PayrollSettingService $service) {}

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
            'pay_frequency' => 'nullable|in:monthly,biweekly,weekly',
            'working_days_per_month' => 'nullable|integer|min:1|max:31',
            'standard_hours_per_day' => 'nullable|numeric|min:1|max:24',
            'overtime_multiplier' => 'nullable|numeric|min:1|max:5',
            'rounding_mode' => 'nullable|in:none,nearest,up,down',
            'proration_method' => 'nullable|in:calendar_days,working_days',
            'personal_relief' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:5',
        ]);

        $settings = $this->service->update($data);
        return response()->json(['message' => 'Payroll settings updated.', 'data' => $settings]);
    }
}
