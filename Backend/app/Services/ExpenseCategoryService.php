<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryService
{
    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return ExpenseCategory::where('business_id', Auth::user()->business_id)
            ->orderBy('name')
            ->withCount('expenses')
            ->get();
    }

    public function create(array $data): ExpenseCategory
    {
        return ExpenseCategory::create([
            'business_id' => Auth::user()->business_id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by'  => Auth::id(),
        ]);
    }

    public function update(int $id, array $data): ExpenseCategory
    {
        $cat = $this->find($id);
        $cat->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        return $cat->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): ExpenseCategory
    {
        return ExpenseCategory::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
