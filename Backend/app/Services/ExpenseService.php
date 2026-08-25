<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Expense::where('business_id', Auth::user()->business_id)
            ->with(['category:id,name', 'branch:id,name', 'creator:id,name'])
            ->orderByDesc('expense_date');

        if (!empty($filters['category_id'])) {
            $query->where('expense_category_id', $filters['category_id']);
        }
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('expense_date', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('expense_date', '<=', $filters['to']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Expense
    {
        return Expense::create([
            'business_id'         => Auth::user()->business_id,
            'expense_category_id' => $data['expense_category_id'],
            'branch_id'           => $data['branch_id'] ?? null,
            'amount'              => $data['amount'],
            'description'         => $data['description'] ?? null,
            'expense_date'        => $data['expense_date'],
            'payment_method'      => $data['payment_method'] ?? 'cash',
            'reference'           => $data['reference'] ?? null,
            'created_by'          => Auth::id(),
        ]);
    }

    public function update(int $id, array $data): Expense
    {
        $expense = $this->find($id);
        $expense->update([
            'expense_category_id' => $data['expense_category_id'],
            'branch_id'           => $data['branch_id'] ?? null,
            'amount'              => $data['amount'],
            'description'         => $data['description'] ?? null,
            'expense_date'        => $data['expense_date'],
            'payment_method'      => $data['payment_method'] ?? 'cash',
            'reference'           => $data['reference'] ?? null,
        ]);
        return $expense->load(['category:id,name', 'branch:id,name', 'creator:id,name']);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): Expense
    {
        return Expense::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function summary(): array
    {
        $businessId = Auth::user()->business_id;
        $today      = now()->toDateString();
        $month      = now()->format('Y-m');

        return [
            'today'       => Expense::where('business_id', $businessId)->whereDate('expense_date', $today)->sum('amount'),
            'this_month'  => Expense::where('business_id', $businessId)->whereYear('expense_date', now()->year)->whereMonth('expense_date', now()->month)->sum('amount'),
            'total'       => Expense::where('business_id', $businessId)->sum('amount'),
        ];
    }
}
