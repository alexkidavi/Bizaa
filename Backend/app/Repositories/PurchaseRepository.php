<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;

class PurchaseRepository
{
    public function __construct(protected Purchase $model) {}

    /**
     * Paginated list of purchases for a business.
     */
public function getAllForBusiness(int $businessId, int $perPage = 20): LengthAwarePaginator
{
    $paginated = $this->model
        ->forBusiness($businessId)
        ->with(['supplier', 'branches', 'user'])
        ->latest('purchase_date')
        ->paginate($perPage);

    $paginated->getCollection()->transform(function ($purchase) {
        $paidAmount = $purchase->paid_amount;
        $grandTotal = $purchase->grand_total;
        
        if ($paidAmount >= $grandTotal) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }
        
        $balance = $grandTotal - $paidAmount;

        return [
            'id' => Crypt::encryptString($purchase->id),
            'reference' => $purchase->reference,
            'subtotal' => $purchase->subtotal ?? $grandTotal,
            'total_discount' => $purchase->total_discount ?? 0,
            'total_tax' => $purchase->total_tax ?? 0,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'balance' => $balance,
            'payment_status' => $paymentStatus,  // Add this
            'notes' => $purchase->notes,
            'supplier' => $purchase->supplier ? [
                'id'   => $purchase->supplier->id,
                'name' => $purchase->supplier->name,
            ] : null,
            'branches' => $purchase->branches->map(function ($branch) {
                return [
                    'id'   => $branch->id,
                    'name' => $branch->name,
                ];
            })->values(),
            'purchase_date' => $purchase->purchase_date,
        ];
    });

    return $paginated;
}

    /**
     * Aggregate totals across ALL of a business's purchases (not paginated).
     */
    public function getSummaryForBusiness(int $businessId): array
    {
        $summary = $this->model
            ->forBusiness($businessId)
            ->selectRaw('
                COUNT(*) as total_count,
                COALESCE(SUM(grand_total), 0) as total_value,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(grand_total - paid_amount), 0) as total_balance
            ')
            ->first();

        return [
            'total_count'   => (int) $summary->total_count,
            'total_value'   => (float) $summary->total_value,
            'total_paid'    => (float) $summary->total_paid,
            'total_balance' => (float) $summary->total_balance,
        ];
    }

    /**
     * Find a single purchase with all relations eagerly loaded.
     */
    public function findById(string $id, int $businessId): Purchase
    {
        $id=Crypt::decryptString($id);
        return $this->model
            ->forBusiness($businessId)
            ->with([
                'supplier',
                'branches',
                'business',
                'items.product',
                'items.branch',
                'payments.user',
                'user',
            ])
            ->findOrFail($id);
    }

    /**
     * Persist a new purchase header row.
     */
    public function create(array $data): Purchase
    {
        return $this->model->create($data);
    }

    /**
     * Update a purchase header row.
     */
    public function update(Purchase $purchase, array $data): Purchase
    {
        $purchase->update($data);
        return $purchase->refresh();
    }

    /**
     * Soft-delete a purchase.
     */
    public function delete(Purchase $purchase): void
    {
        $purchase->delete();
    }

    /**
     * Sync branches to a purchase via the pivot table.
     * $branchData format: [ branch_id => ['subtotal' => x, ...], ... ]
     */
    public function syncBranches(Purchase $purchase, array $branchData): void
    {
        $purchase->branches()->sync($branchData);
    }

    /**
     * Replace all items for a purchase (delete then bulk-insert).
     * $items is an array of attribute arrays, each already containing purchase_id.
     */
    public function replaceItems(Purchase $purchase, array $items): void
    {
        $purchase->items()->delete();

        if (!empty($items)) {
            $purchase->items()->insert($items);
        }
    }

    /**
     * Append a payment record to a purchase.
     */
    public function recordPayment(Purchase $purchase, array $data): PurchasePayment
    {
        return $purchase->payments()->create($data);
    }
}
