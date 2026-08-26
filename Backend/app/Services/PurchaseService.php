<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockHistory;
use App\Repositories\PurchaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(protected PurchaseRepository $repo, protected DigitaxService $digitax) {}

    // ─────────────────────────────────────────────────────────
    // READ
    // ─────────────────────────────────────────────────────────

    /**
     * Paginated list for the authenticated user's business.
     */
    public function list(int $perPage = 20): LengthAwarePaginator
    {
        return $this->repo->getAllForBusiness(
            Auth::user()->business_id,
            $perPage
        );
    }

    /**
     * Aggregate totals across all of the business's purchases (unpaginated).
     */
    public function summary(): array
    {
        return $this->repo->getSummaryForBusiness(Auth::user()->business_id);
    }

    /**
     * Single purchase detail.
     */
    public function find(string $id): Purchase
    {
        return $this->repo->findById($id, Auth::user()->business_id);
    }

    // ─────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────

    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            // 1. Resolve financials
            [$paidAmount, $paymentStatus] = $this->resolvePayment(
                $data['payment_type'],
                $data['paid_amount'] ?? 0,
                $data['grand_total']
            );

            // 2. Create purchase header
            $purchase = $this->repo->create([
                'business_id'       => Auth::user()->business_id,
                'user_id'           => Auth::user()->id,
                'supplier_id'       => $data['supplier_id'],
                'reference'         => $data['reference'] ?? null,
                'purchase_date'     => $data['purchase_date'],
                'notes'             => $data['notes'] ?? null,
                'subtotal'          => $data['subtotal'],
                'total_discount'    => $data['total_discount'],
                'total_tax'         => $data['total_tax'],
                'grand_total'       => $data['grand_total'],
                'paid_amount'       => $paidAmount,
                'balance'           => $data['grand_total'] - $paidAmount,
                'payment_type'      => $data['payment_type'],
                'payment_status'    => $paymentStatus,
                'payment_method'    => $data['payment_type'] !== 'none' ? ($data['payment_method'] ?? null) : null,
                'payment_reference' => $data['payment_reference'] ?? null,
            ]);

            // 3. Sync branches + collect items
            [$branchSync, $allItems] = $this->prepareBranchData($purchase->id, $data['branches']);
            $this->repo->syncBranches($purchase, $branchSync);
            $this->repo->replaceItems($purchase, $allItems);

            // 4. Record payment ledger entry
            if ($data['payment_type'] !== 'none' && $paidAmount > 0) {
                $this->repo->recordPayment($purchase, [
                    'user_id'           => $user->id,
                    'amount'            => $paidAmount,
                    'payment_method'    => $data['payment_method'],
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'payment_date'      => $data['purchase_date'],
                    'notes'             => null,
                ]);
            }

            // 5. Update stock per branch
            $this->incrementStock($purchase, $allItems);

            return $this->repo->findById(Crypt::encryptString($purchase->id), $user->business_id);
        });
    }

    // ─────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────

    public function update(int $id, array $data): Purchase
    {
        return DB::transaction(function () use ($id, $data) {
            $user     = Auth::user();
            $purchase = $this->repo->findById($id, $user->business_id);

            [$paidAmount, $paymentStatus] = $this->resolvePayment(
                $data['payment_type'],
                $data['paid_amount'] ?? 0,
                $data['grand_total']
            );

            $this->repo->update($purchase, [
                'supplier_id'       => $data['supplier_id'],
                'reference'         => $data['reference'] ?? $purchase->reference,
                'purchase_date'     => $data['purchase_date'],
                'notes'             => $data['notes'] ?? null,
                'subtotal'          => $data['subtotal'],
                'total_discount'    => $data['total_discount'],
                'total_tax'         => $data['total_tax'],
                'grand_total'       => $data['grand_total'],
                'paid_amount'       => $paidAmount,
                'balance'           => $data['grand_total'] - $paidAmount,
                'payment_type'      => $data['payment_type'],
                'payment_status'    => $paymentStatus,
                'payment_method'    => $data['payment_type'] !== 'none' ? ($data['payment_method'] ?? null) : null,
                'payment_reference' => $data['payment_reference'] ?? null,
            ]);

            [$branchSync, $allItems] = $this->prepareBranchData($purchase->id, $data['branches']);

            $this->repo->syncBranches($purchase, $branchSync);
            $this->repo->replaceItems($purchase, $allItems);

            return $this->repo->findById($purchase->id, $user->business_id);
        });
    }

    // ─────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────

    public function delete($id): void
    {
        $purchase = $this->repo->findById($id, Auth::user()->business_id);
        $this->repo->delete($purchase);
    }

    // ─────────────────────────────────────────────────────────
    // RECORD ADDITIONAL PAYMENT
    // ─────────────────────────────────────────────────────────

    public function addPayment(int $id, array $data): Purchase
    {
        return DB::transaction(function () use ($id, $data) {
            $user     = Auth::user();
            $purchase = $this->repo->findById(Crypt::encryptString($id), $user->business_id);

            $this->repo->recordPayment($purchase, [
                'user_id'           => $user->id,
                'amount'            => $data['amount'],
                'payment_method'    => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_date'      => $data['payment_date'],
                'notes'             => $data['notes'] ?? null,
            ]);

            $totalPaid = $purchase->payments()->sum('amount');
            $newStatus = match (true) {
                $totalPaid <= 0                    => 'unpaid',
                $totalPaid >= $purchase->grand_total => 'paid',
                default                             => 'partial',
            };

            $purchase->update([
                'paid_amount'    => $totalPaid,
                'balance'        => max(0, $purchase->grand_total - $totalPaid),
                'payment_status' => $newStatus,
            ]);
            return $this->repo->findById(Crypt::encryptString($purchase->id), $user->business_id);
        });
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    /**
     * Resolve paid amount and payment status from payment type.
     * Returns [paidAmount, paymentStatus]
     */
    private function resolvePayment(string $type, float $paid, float $grandTotal): array
    {
        if ($type === 'none') {
            return [0.0, 'unpaid'];
        }

        $paidAmount = min((float) $paid, $grandTotal);

        $status = match (true) {
            $paidAmount <= 0         => 'unpaid',
            $paidAmount >= $grandTotal => 'paid',
            default                  => 'partial',
        };

        return [$paidAmount, $status];
    }

    /**
     * Build the branch sync array and flat items array from the branches payload.
     * Returns [$branchSync, $allItems]
     */
    private function prepareBranchData(int $purchaseId, array $branches): array
    {

        $branchSync = [];
        $allItems   = [];
        $now        = now();

        foreach ($branches as $branchData) {
            $branchId = $branchData['branch_id'];

            $branchSync[$branchId] = [
                'subtotal'   => $branchData['subtotal'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($branchData['items'] as $item) {
                $allItems[] = [
                    'purchase_id'  => $purchaseId,
                    'branch_id'    => $branchId,
                    'product_id'   => ($item['product_id']),
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'discount'     => $item['discount'] ?? 0,
                    'tax'          => $item['tax'] ?? 0,
                    'line_total'   => $item['line_total'],
                    'expiry_date'  => $item['expiry_date'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }
        }

        return [$branchSync, $allItems];
    }

    /**
     * Increment stock for each product per branch, same as the POS sale
     * flow decrements directly at checkout. Also records a StockHistory
     * row per line (previously purchases left no audit trail at all,
     * despite 'purchase' already being a supported history type) and
     * syncs the movement to DigiTax.
     */
    private function incrementStock(Purchase $purchase, array $items): void
    {
        $user = Auth::user();

        foreach ($items as $item) {
            DB::table('product_branch')->updateOrInsert(
                [
                    'product_id' => ($item['product_id']),
                    'branch_id'  => $item['branch_id'],
                ],
                [
                    'stock'   => DB::raw("stock + {$item['quantity']}"),
                    'updated_at' => now(),
                ]
            );

            // reference is unique per row, so a multi-line purchase needs a
            // per-line suffix — still traceable back to the purchase itself.
            $history = StockHistory::create([
                'product_id' => $item['product_id'],
                'branch_id' => $item['branch_id'],
                'business_id' => $user->business_id,
                'user_id' => $user->id,
                'reference' => ($purchase->reference ?? ('PUR-' . $purchase->id)) . '-' . strtoupper(uniqid()),
                'type' => 'purchase',
                'action' => 'add',
                'quantity' => $item['quantity'],
                'notes' => 'Stock received from purchase',
            ]);

            $product = Product::find($item['product_id']);

            if ($product) {
                $this->digitax->syncStockAdjustment($product, $history, (float) $item['quantity']);
            }
        }
    }
}
