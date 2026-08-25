<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\{JsonResponse, Request};

class ExpenseCategoryController extends Controller
{
    public function __construct(protected ExpenseCategoryService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ExpenseCategory::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ExpenseCategory::class);
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $cat = $this->service->create($data);
        return response()->json(['message' => 'Category created.', 'data' => $cat], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $cat = $this->service->find($id);
        $this->authorize('update', $cat);
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $cat = $this->service->update($id, $data);
        return response()->json(['message' => 'Category updated.', 'data' => $cat]);
    }

    public function destroy(int $id): JsonResponse
    {
        $cat = $this->service->find($id);
        $this->authorize('delete', $cat);
        $this->service->delete($id);
        return response()->json(['message' => 'Category deleted.']);
    }
}
