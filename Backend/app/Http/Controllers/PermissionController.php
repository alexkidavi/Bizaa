<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function __construct(protected PermissionService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->service->getAll()]);
    }
}
