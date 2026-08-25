<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\{JsonResponse, Request};

class DepartmentController extends Controller
{
    public function __construct(protected DepartmentService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Department::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Department::class);
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $dept = $this->service->create($data);
        return response()->json(['message' => 'Department created.', 'data' => $dept], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $dept = $this->service->find($id);
        $this->authorize('update', $dept);
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $dept = $this->service->update($id, $data);
        return response()->json(['message' => 'Department updated.', 'data' => $dept]);
    }

    public function destroy(int $id): JsonResponse
    {
        $dept = $this->service->find($id);
        $this->authorize('delete', $dept);
        $this->service->delete($id);
        return response()->json(['message' => 'Department deleted.']);
    }
}
