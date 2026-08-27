<?php

namespace App\Http\Controllers;

use App\Models\CrmTag;
use App\Services\CrmTagService;
use Illuminate\Http\{JsonResponse, Request};

class CrmTagController extends Controller
{
    public function __construct(protected CrmTagService $service) {}

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', CrmTag::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmTag::class);
        $data = $request->validate($this->rules());
        $tag = $this->service->create($data);
        return response()->json(['message' => 'Tag created.', 'data' => $tag], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tag = $this->service->find($id);
        $this->authorize('update', $tag);
        $data = $request->validate($this->rules());
        $tag = $this->service->update($id, $data);
        return response()->json(['message' => 'Tag updated.', 'data' => $tag]);
    }

    public function destroy(int $id): JsonResponse
    {
        $tag = $this->service->find($id);
        $this->authorize('delete', $tag);
        $this->service->delete($id);
        return response()->json(['message' => 'Tag deleted.']);
    }
}
