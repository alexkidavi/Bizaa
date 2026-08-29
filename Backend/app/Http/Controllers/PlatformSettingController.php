<?php

namespace App\Http\Controllers;

use App\Services\PlatformSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Public, unauthenticated read of platform branding/contact info — the
 * landing page and Privacy page fetch this instead of hardcoding it, so
 * it can be edited from Super Admin > Settings without a deploy. Nothing
 * returned here is sensitive; the write side (SuperAdmin\PlatformSettingController)
 * is gated behind the superadmin middleware.
 */
class PlatformSettingController extends Controller
{
    public function __construct(protected PlatformSettingService $service) {}

    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->service->getOrCreate()]);
    }
}
