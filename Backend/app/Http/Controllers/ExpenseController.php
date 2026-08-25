<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\{JsonResponse, Request};

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);
        $expenses = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['category_id', 'branch_id', 'from', 'to'])
        );
        return response()->json($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Expense::class);
        $data = $request->validate([
            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'branch_id'           => 'nullable|integer|exists:branches,id',
            'amount'              => 'required|numeric|min:0.01',
            'description'         => 'nullable|string|max:255',
            'expense_date'        => 'required|date',
            'payment_method'      => 'nullable|in:cash,bank,mobile',
            'reference'           => 'nullable|string|max:100',
        ]);

        $expense = $this->service->create($data);
        return response()->json([
            'message' => 'Expense recorded.',
            'data'    => $expense->load(['category:id,name', 'branch:id,name']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $expense = $this->service->find($id);
        $this->authorize('update', $expense);
        $data = $request->validate([
            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'branch_id'           => 'nullable|integer|exists:branches,id',
            'amount'              => 'required|numeric|min:0.01',
            'description'         => 'nullable|string|max:255',
            'expense_date'        => 'required|date',
            'payment_method'      => 'nullable|in:cash,bank,mobile',
            'reference'           => 'nullable|string|max:100',
        ]);

        $expense = $this->service->update($id, $data);
        return response()->json(['message' => 'Expense updated.', 'data' => $expense]);
    }

    public function destroy(int $id): JsonResponse
    {
        $expense = $this->service->find($id);
        $this->authorize('delete', $expense);
        $this->service->delete($id);
        return response()->json(['message' => 'Expense deleted.']);
    }

    public function summary(): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);
        return response()->json(['data' => $this->service->summary()]);
    }
}
