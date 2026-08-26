<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $amount = (float) $data['amount'];
            $amountPaid = min((float) ($data['amount_paid'] ?? 0), $amount);

            $expense = Expense::create([
                'business_id'         => Auth::user()->business_id,
                'expense_category_id' => $data['expense_category_id'],
                'branch_id'           => $data['branch_id'] ?? null,
                'amount'              => $amount,
                'amount_paid'         => $amountPaid,
                'balance'             => max(0, $amount - $amountPaid),
                'payment_status'      => $this->resolveStatus($amount, $amountPaid),
                'description'         => $data['description'] ?? null,
                'expense_date'        => $data['expense_date'],
                'payment_method'      => $data['payment_method'] ?? 'cash',
                'reference'           => $data['reference'] ?? null,
                'created_by'          => Auth::id(),
            ]);

            if ($amountPaid > 0) {
                $expense->payments()->create([
                    'user_id'           => Auth::id(),
                    'amount'            => $amountPaid,
                    'payment_method'    => $data['payment_method'] ?? 'cash',
                    'payment_reference' => $data['reference'] ?? null,
                    'payment_date'      => $data['expense_date'],
                ]);
            }

            return $expense->load(['category:id,name', 'branch:id,name', 'creator:id,name']);
        });
    }

    public function update(int $id, array $data): Expense
    {
        $expense = $this->find($id);
        $amount = (float) $data['amount'];
        $alreadyPaid = (float) $expense->payments()->sum('amount');
        $amountPaid = min($alreadyPaid, $amount);

        $expense->update([
            'expense_category_id' => $data['expense_category_id'],
            'branch_id'           => $data['branch_id'] ?? null,
            'amount'              => $amount,
            'amount_paid'         => $amountPaid,
            'balance'             => max(0, $amount - $amountPaid),
            'payment_status'      => $this->resolveStatus($amount, $amountPaid),
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

    public function payments(int $id): Collection
    {
        return $this->find($id)->payments()->with('user:id,name')->orderByDesc('payment_date')->orderByDesc('id')->get();
    }

    public function addPayment(int $id, array $data): Expense
    {
        return DB::transaction(function () use ($id, $data) {
            $expense = $this->find($id);
            $amount = (float) $data['amount'];

            if ($amount > (float) $expense->balance) {
                throw new InvalidArgumentException('Payment amount exceeds the outstanding balance.');
            }

            $expense->payments()->create([
                'user_id'           => Auth::id(),
                'amount'            => $amount,
                'payment_method'    => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'payment_date'      => $data['payment_date'],
            ]);

            $totalPaid = (float) $expense->payments()->sum('amount');

            $expense->update([
                'amount_paid'    => $totalPaid,
                'balance'        => max(0, (float) $expense->amount - $totalPaid),
                'payment_status' => $this->resolveStatus((float) $expense->amount, $totalPaid),
            ]);

            return $expense->fresh(['category:id,name', 'branch:id,name', 'creator:id,name', 'payments']);
        });
    }

    protected function resolveStatus(float $amount, float $paid): string
    {
        return match (true) {
            $paid <= 0     => 'pending',
            $paid >= $amount => 'paid',
            default        => 'partially_paid',
        };
    }

    public function summary(): array
    {
        $businessId = Auth::user()->business_id;
        $today      = now()->toDateString();

        return [
            'today'       => Expense::where('business_id', $businessId)->whereDate('expense_date', $today)->sum('amount'),
            'this_month'  => Expense::where('business_id', $businessId)->whereYear('expense_date', now()->year)->whereMonth('expense_date', now()->month)->sum('amount'),
            'total'       => Expense::where('business_id', $businessId)->sum('amount'),
            'outstanding' => Expense::where('business_id', $businessId)->sum('balance'),
        ];
    }
}
