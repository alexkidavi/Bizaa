<?php

namespace App\Http\Controllers;

use App\Models\CrmSource;
use App\Services\CrmSourceService;
use Illuminate\Http\{JsonResponse, Request};

class CrmSourceController extends Controller
{
    public function __construct(protected CrmSourceService $service) {}

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CrmSource::class);
        return response()->json(['data' => $this->service->list($request->boolean('active_only'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmSource::class);
        $data = $request->validate($this->rules());
        $source = $this->service->create($data);
        return response()->json(['message' => 'Source created.', 'data' => $source], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $source = $this->service->find($id);
        $this->authorize('update', $source);
        $data = $request->validate($this->rules());
        $source = $this->service->update($id, $data);
        return response()->json(['message' => 'Source updated.', 'data' => $source]);
    }

    public function destroy(int $id): JsonResponse
    {
        $source = $this->service->find($id);
        $this->authorize('delete', $source);
        $this->service->delete($id);
        return response()->json(['message' => 'Source deleted.']);
    }
}
