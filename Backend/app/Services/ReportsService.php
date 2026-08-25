<?php

namespace App\Services;

use App\Repositories\ReportsRepository;
use Carbon\Carbon;

class ReportsService
{
    public function __construct(protected ReportsRepository $repo) {}

    /* ─── Date preset resolver ───────────────────
     |  Accepts: today | this_week | this_month | custom
     |  Returns: [from, to] as date strings
     ──────────────────────────────────────────── */
    public function resolveDateRange(string $preset, ?string $from = null, ?string $to = null): array
    {
        return match ($preset) {
            'today'      => [Carbon::today()->toDateString(),            Carbon::today()->toDateString()],
            'this_week'  => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
            'this_month' => [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()],
            default      => [$from, $to], // 'custom' — caller supplies from/to
        };
    }

    /* ─── 1. Close Day Report ───────────────── */
    public function closeDayReport(string $from, string $to, int $businessId): array
    {
        return $this->repo->getCloseDayReport($from, $to, $businessId);
    }

    /* ─── 2. Items Sale Report ──────────────── */
    public function itemsSaleReport(string $from, string $to, int $businessId, ?int $branchId = null): array
    {
        $items = $this->repo->getItemsSaleReport($from, $to, $businessId, $branchId);

        return [
            'items'   => $items,
            'summary' => [
                'total_products' => $items->count(),
                'total_qty_sold' => $items->sum('qty_sold'),
                'gross_revenue'  => $items->sum('gross_revenue'),
                'total_discount' => $items->sum('total_discount'),
                'net_revenue'    => $items->sum('net_revenue'),
                'total_cost'     => $items->sum('total_cost'),
                'gross_profit'   => $items->sum('gross_profit'),
                'profit_margin'  => $items->sum('net_revenue') > 0
                    ? round(($items->sum('gross_profit') / $items->sum('net_revenue')) * 100, 2)
                    : 0,
            ],
        ];
    }

    /* ─── 3. Sales by Customer ──────────────── */
    public function salesByCustomer(string $from, string $to, int $businessId): array
    {
        $rows = $this->repo->getSalesByCustomer($from, $to, $businessId);

        return [
            'customers' => $rows,
            'summary'   => [
                'total_customers' => $rows->count(),
                'gross_total'     => $rows->sum('gross_total'),
                'total_discount'  => $rows->sum('total_discount'),
                'total_paid'      => $rows->sum('total_paid'),
                'balance_due'     => $rows->sum('balance_due'),
                'refund_count'    => $rows->sum('refund_count'),
            ],
        ];
    }

    /* ─── 4. Sales by Branch ────────────────── */
    public function salesByBranch(string $from, string $to, int $businessId): array
    {
        $rows = $this->repo->getSalesByBranch($from, $to, $businessId);

        return [
            'branches' => $rows,
            'summary'  => [
                'total_branches' => $rows->count(),
                'gross_total'    => $rows->sum('gross_total'),
                'total_discount' => $rows->sum('total_discount'),
                'total_paid'     => $rows->sum('total_paid'),
                'balance_due'    => $rows->sum('balance_due'),
                'refund_count'   => $rows->sum('refund_count'),
            ],
        ];
    }

    /* ─── 5. Items Ledger ───────────────────── */
    public function itemsLedger(string $from, string $to, int $businessId, ?int $productId = null): array
    {
        $rows = $this->repo->getItemsLedger($from, $to, $businessId, $productId);

        return [
            'ledger'  => $rows,
            'summary' => [
                'total_in'     => $rows->sum('qty_in'),
                'total_out'    => $rows->sum('qty_out'),
                'total_amount' => $rows->sum('amount'),
            ],
        ];
    }

    /* ─── 6. Customer Balances ──────────────── */
    public function customerBalances(int $businessId, ?string $search = null): array
    {
        $rows = $this->repo->getCustomerBalances($businessId, $search);

        return [
            'customers' => $rows,
            'summary'   => [
                'total_customers'   => $rows->count(),
                'total_invoiced'    => $rows->sum('total_invoiced'),
                'total_paid'        => $rows->sum('total_paid'),
                'total_outstanding' => $rows->sum('outstanding'),
                'total_refunded'    => $rows->sum('total_refunded'),
            ],
        ];
    }

    /* ─── 7. Supplier Balances ──────────────── */
    public function supplierBalances(int $businessId, ?string $search = null): array
    {
        $rows = $this->repo->getSupplierBalances($businessId, $search);

        return [
            'suppliers' => $rows,
            'summary'   => [
                'total_suppliers'   => $rows->count(),
                'total_purchased'   => $rows->sum('total_purchased'),
                'total_paid'        => $rows->sum('total_paid'),
                'total_outstanding' => $rows->sum('outstanding'),
            ],
        ];
    }
     /* ─── 8. Sale Payments ──────────────────── */
    public function salePayments(string $from, string $to, int $businessId, ?string $method = null, ?string $search = null): array
    {
        [$rows, $summary] = [
            $this->repo->getSalePayments($from, $to, $businessId, $method, $search),
            $this->repo->getSalePaymentsSummary($from, $to, $businessId),
        ];
 
        return ['payments' => $rows, 'summary' => $summary];
    }
 
    /* ─── 9. Purchase Payments ──────────────── */
    public function purchasePayments(string $from, string $to, int $businessId, ?string $method = null, ?string $search = null): array
    {
        [$rows, $summary] = [
            $this->repo->getPurchasePayments($from, $to, $businessId, $method, $search),
            $this->repo->getPurchasePaymentsSummary($from, $to, $businessId),
        ];
 
        return ['payments' => $rows, 'summary' => $summary];
    }
}
