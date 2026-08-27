<?php

namespace App\Http\Controllers;

use App\Services\HrmLookupTypeService;
use Illuminate\Http\{JsonResponse, Request};

abstract class HrmLookupTypeController extends Controller
{
    protected HrmLookupTypeService $service;
    protected string $modelClass;
    protected string $label;

    public function __construct()
    {
        $this->service = new HrmLookupTypeService($this->modelClass);
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', $this->modelClass);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', $this->modelClass);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $type = $this->service->create($data);
        return response()->json(['message' => "{$this->label} created.", 'data' => $type], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $type = $this->service->update($id, $data);
        return response()->json(['message' => "{$this->label} updated.", 'data' => $type]);
    }

    public function destroy(int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('delete', $existing);
        $this->service->delete($id);
        return response()->json(['message' => "{$this->label} deleted."]);
    }
}
