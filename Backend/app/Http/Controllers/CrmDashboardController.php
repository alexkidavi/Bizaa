<?php

namespace App\Http\Controllers;

use App\Services\CrmDashboardService;
use Illuminate\Http\{JsonResponse, Request};

class CrmDashboardController extends Controller
{
    public function __construct(protected CrmDashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\CrmContact::class);

        $data = $this->service->full(
            $request->query('from'),
            $request->query('to')
        );

        return response()->json($data);
    }
}
