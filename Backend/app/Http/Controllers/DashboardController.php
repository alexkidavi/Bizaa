<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
     protected $service;
   public function __construct(DashboardService $service)
{
    $this->service = $service;
}
public function index(Request $request): JsonResponse
{
   $filter = $request->input('filter.period', 'today');
    $businessId = $request->user()->business_id;
    $branchId = $request->input('filter.branch_id')
    ? (int) $request->input('filter.branch_id')
    : null;

    $startDate = null;
    $endDate = null;
    if ($filter === 'custom') {
        $request->validate([
            'filter.start_date' => 'required|date',
            'filter.end_date'   => 'required|date|after_or_equal:filter.start_date',
        ]);
        $startDate = $request->input('filter.start_date');
        $endDate   = $request->input('filter.end_date');
    }

    $data = $this->service->getDashboardData($filter, $businessId, $branchId, $startDate, $endDate);
    $data['user'] = [
        'id'   => $request->user()->id,
        'name' => $request->user()->name,
    ];

    return response()->json(['data' => $data]);
}
}