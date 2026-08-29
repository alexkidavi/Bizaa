<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PlatformSettingService;
use Illuminate\Http\{JsonResponse, Request};

class PlatformSettingController extends Controller
{
    public function __construct(protected PlatformSettingService $service) {}

    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->service->getOrCreate()]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:100',
            'tagline' => 'nullable|string|max:150',
            'support_email' => 'nullable|email|max:150',
            'support_phone' => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'logo' => 'nullable|file|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('platform', 'public');
        } else {
            unset($data['logo']);
        }

        $settings = $this->service->update($data);
        return response()->json(['message' => 'Platform settings updated.', 'data' => $settings]);
    }
}
