<?php

namespace App\Http\Controllers;

use App\Models\CrmPipelineStage;
use App\Services\CrmPipelineStageService;
use Illuminate\Http\{JsonResponse, Request};

class CrmPipelineStageController extends Controller
{
    public function __construct(protected CrmPipelineStageService $service) {}

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
            'default_probability' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', CrmPipelineStage::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmPipelineStage::class);
        $data = $request->validate($this->rules());
        $stage = $this->service->create($data);
        return response()->json(['message' => 'Pipeline stage created.', 'data' => $stage], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $stage = $this->service->find($id);
        $this->authorize('update', $stage);
        $data = $request->validate($this->rules());
        $stage = $this->service->update($id, $data);
        return response()->json(['message' => 'Pipeline stage updated.', 'data' => $stage]);
    }

    public function destroy(int $id): JsonResponse
    {
        $stage = $this->service->find($id);
        $this->authorize('delete', $stage);
        $this->service->delete($id);
        return response()->json(['message' => 'Pipeline stage deleted.']);
    }
}
