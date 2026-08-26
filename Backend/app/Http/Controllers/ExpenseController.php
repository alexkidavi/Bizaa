<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\{JsonResponse, Request};
use InvalidArgumentException;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);
        $expenses = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['category_id', 'branch_id', 'from', 'to', 'payment_status'])
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
            'amount_paid'         => 'nullable|numeric|min:0|lte:amount',
            'description'         => 'nullable|string|max:255',
            'expense_date'        => 'required|date',
            'payment_method'      => 'nullable|in:cash,bank,mobile',
            'reference'           => 'nullable|string|max:100',
        ]);

        $expense = $this->service->create($data);
        return response()->json([
            'message' => 'Expense recorded.',
            'data'    => $expense,
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

    public function payments(int $id): JsonResponse
    {
        $expense = $this->service->find($id);
        $this->authorize('view', $expense);
        return response()->json(['data' => $this->service->payments($id)]);
    }

    public function recordPayment(Request $request, int $id): JsonResponse
    {
        $expense = $this->service->find($id);
        $this->authorize('update', $expense);
        $data = $request->validate([
            'amount'            => 'required|numeric|min:0.01',
            'payment_method'    => 'required|in:cash,bank,mobile',
            'payment_date'      => 'required|date',
            'payment_reference' => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:500',
        ]);

        try {
            $expense = $this->service->addPayment($id, $data);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Payment recorded successfully.', 'data' => $expense]);
    }
}
