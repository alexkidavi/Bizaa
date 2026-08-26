<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsRepository
{
    /* ══════════════════════════════════════════
     |  HELPERS
     ══════════════════════════════════════════ */
    private function dateRange(string $from, string $to): array
    {
        return [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ];
    }

    /* ══════════════════════════════════════════
     |  1. CLOSE DAY REPORT
     |  Structure: Sales → SaleItems → Products
     |  Includes: gross sales, refunds, discounts, profit summary
     ══════════════════════════════════════════ */
  public function getCloseDayReport(string $from, string $to, int $businessId): array
{
    [$start, $end] = $this->dateRange($from, $to);

    /* ─────────────────────────────
     | SALES SUMMARY
    ───────────────────────────── */
    $sales = DB::table('sales')
        ->where('business_id', $businessId)
        ->whereBetween('created_at', [$start, $end])
     
        ->selectRaw('
            COUNT(*) as total_transactions,
            COALESCE(SUM(total), 0) as gross_sales,
            COALESCE(SUM(discount), 0) as total_discounts,
            COALESCE(SUM(tax), 0) as total_tax
        ')
        ->first();

    /* ─────────────────────────────
     | TOTAL PAID (from payments table)
    ───────────────────────────── */
    $paymentsTotal = DB::table('sale_payments')
        ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
        ->where('sales.business_id', $businessId)
        ->whereBetween('sale_payments.created_at', [$start, $end])
        // 
        // 
        ->sum('sale_payments.amount');

    /* ─────────────────────────────
     | COST OF GOODS SOLD
    ───────────────────────────── */
     $cogs = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->join('products', 'products.id', '=', 'sale_items.product_id')
        ->where('sales.business_id', $businessId)
        ->whereBetween('sales.created_at', [$start, $end])
        ->selectRaw('
            COALESCE(SUM(sale_items.quantity * products.buying_price), 0) as total_cogs,
            COALESCE(SUM(sale_items.quantity), 0) as total_qty_sold
        ')
        ->first();

    /* ─────────────────────────────
     | PAYMENT BREAKDOWN
    ───────────────────────────── */
    $payments = DB::table('sale_payments')
        ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
        ->where('sales.business_id', $businessId)
        ->whereBetween('sale_payments.created_at', [$start, $end])
        // 
        ->selectRaw('payment_method, SUM(amount) as total')
        ->groupBy('payment_method')
        ->get()
        ->keyBy('payment_method');

    /* ─────────────────────────────
     | TOP SELLING ITEMS
    ───────────────────────────── */
    $topItems = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->join('products', 'products.id', '=', 'sale_items.product_id')
        ->where('sales.business_id', $businessId)
        ->whereBetween('sales.created_at', [$start, $end])
        ->selectRaw('
            products.name as product_name,
            SUM(sale_items.quantity) as qty_sold,
            SUM(sale_items.quantity * sale_items.unit_price) as revenue
        ')
        ->groupBy('products.id', 'products.name')
        ->orderByDesc('revenue')
        ->limit(5)
        ->get();

    /* ─────────────────────────────
     | SALES BY BRANCH
    ───────────────────────────── */
    $byBranch = DB::table('sales')
        ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
        ->where('sales.business_id', $businessId)
        ->whereBetween('sales.created_at', [$start, $end])

        ->selectRaw('
            COALESCE(branches.name, "Head Office") as branch,
            COALESCE(SUM(total), 0) as total,
            COUNT(*) as count
        ')
        ->groupBy('branches.id', 'branches.name')
        ->get();

    /* ─────────────────────────────
     | DAILY TOTALS
    ───────────────────────────── */
    $dailyTotals = DB::table('sales')
        ->where('business_id', $businessId)
        ->whereBetween('created_at', [$start, $end])
        ->selectRaw('
            DATE(created_at) as date,
            COUNT(*) as transactions,
            COALESCE(SUM(total), 0) as total
        ')
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date')
        ->get();

    /* ─────────────────────────────
     | CALCULATIONS
    ───────────────────────────── */
    $grossSales   = (float) ($sales->gross_sales ?? 0);
    $discounts    = (float) ($sales->total_discounts ?? 0);
    $tax          = (float) ($sales->total_tax ?? 0);
    $cogsTotal    = (float) ($cogs->total_cogs ?? 0);

    $netSales     = $grossSales - $discounts;
    $grossProfit  = $netSales - $cogsTotal;
    $profitMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 2) : 0;

    /* ─────────────────────────────
     | RETURN
    ───────────────────────────── */
    return [
        'from'               => $from,
        'to'                 => $to,

        // Sales
        'total_transactions' => (int) ($sales->total_transactions ?? 0),
        'gross_sales'        => $grossSales,
        'total_discounts'    => $discounts,
        'total_tax'          => $tax,
        'total_paid'         => (float) $paymentsTotal,
        'total_balance_due'  => max(0, $grossSales - (float) $paymentsTotal),
        // No refunds concept exists in this schema yet.
        'refund_total'       => 0,
        'refund_count'       => 0,
        'net_sales'          => $netSales,

        // COGS & Profit
        'total_cogs'         => $cogsTotal,
        'total_qty_sold'     => (int) ($cogs->total_qty_sold ?? 0),
        'gross_profit'       => $grossProfit,
        'profit_margin'      => $profitMargin,

        // Payment breakdown
        'payment_breakdown'  => [
            'cash'  => (float) ($payments['cash']->total ?? 0),
            'mpesa' => (float) ($payments['mpesa']->total ?? 0),
            'card'  => (float) ($payments['card']->total ?? 0),
            'other' => (float) (
                $payments->whereNotIn('payment_method', ['cash', 'mpesa', 'card'])
                    ->sum('total')
            ),
        ],

        // Details
        'top_items'          => $topItems,
        'sales_by_branch'    => $byBranch,
        'daily_totals'       => $dailyTotals,
    ];
}
    /* ══════════════════════════════════════════
     |  2. ITEMS SALE REPORT
     ══════════════════════════════════════════ */
    public function getItemsSaleReport(string $from, string $to, int $businessId, ?int $branchId = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);

        return DB::table('sale_items')
            ->join('sales',    'sales.id',    '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.business_id', $businessId)
            ->whereBetween('sales.created_at', [$start, $end])
            
            
            ->when($branchId, fn($q) => $q->where('sales.branch_id', $branchId))
            ->selectRaw('
                products.id,
                products.name                                          as product_name,
                products.product_code,
                SUM(sale_items.quantity)                               as qty_sold,
                AVG(sale_items.unit_price)                             as avg_price,
                SUM(sale_items.quantity * sale_items.unit_price)       as gross_revenue,
                SUM(COALESCE(sale_items.discount, 0))           as total_discount,
                SUM(sale_items.quantity * sale_items.unit_price)       as net_revenue,
                SUM(sale_items.quantity * products.buying_price)          as total_cost,
                SUM(sale_items.quantity * sale_items.unit_price)
                  - SUM(sale_items.quantity * products.buying_price)      as gross_profit
            ')
            ->groupBy('products.id', 'products.name', 'products.product_code')
            ->orderByDesc('net_revenue')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  3. SALES BY CUSTOMER
     ══════════════════════════════════════════ */
   public function getSalesByCustomer(string $from, string $to, int $businessId): \Illuminate\Support\Collection
{
    [$start, $end] = $this->dateRange($from, $to);

    // Payments pre-aggregated to one row per sale *before* joining to sales
    // — joining the raw sale_payments table directly (the previous
    // approach) fans out one sales row into N rows for a sale with N
    // payments, inflating SUM(sales.total)/SUM(sales.discount) by that
    // same factor for any split payment.
    $paymentsPerSale = DB::table('sale_payments')
        ->groupBy('sale_id')
        ->selectRaw('sale_id, SUM(amount) as paid');

    return DB::table('sales')
        ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
        ->leftJoinSub($paymentsPerSale, 'sp', 'sp.sale_id', '=', 'sales.id')

        ->where('sales.business_id', $businessId)
        ->whereBetween('sales.created_at', [$start, $end])


        ->selectRaw('
            COALESCE(customers.id, 0) as customer_id,
            COALESCE(customers.name, "Walk-in Customer") as customer_name,
            customers.phone,

            COUNT(DISTINCT sales.id) as total_sales,

            COALESCE(SUM(sales.total), 0) as gross_total,
            COALESCE(SUM(sales.discount), 0) as total_discount,

            COALESCE(SUM(sp.paid), 0) as total_paid,

            COALESCE(SUM(sales.total), 0) - COALESCE(SUM(sp.paid), 0) as balance_due
        ')
        ->groupBy('customers.id', 'customers.name', 'customers.phone')
        ->orderByDesc('gross_total')
        ->get();
}
    /* ══════════════════════════════════════════
     |  4. SALES BY BRANCH
     ══════════════════════════════════════════ */
  public function getSalesByBranch(string $from, string $to, int $businessId): \Illuminate\Support\Collection
{
    [$start, $end] = $this->dateRange($from, $to);

    // See getSalesByCustomer() above for why payments are pre-aggregated
    // per sale before joining, instead of joining sale_payments directly.
    $paymentsPerSale = DB::table('sale_payments')
        ->groupBy('sale_id')
        ->selectRaw('sale_id, SUM(amount) as paid');

    return DB::table('sales')
        ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
        ->leftJoinSub($paymentsPerSale, 'sp', 'sp.sale_id', '=', 'sales.id')

        ->where('sales.business_id', $businessId)
        ->whereBetween('sales.created_at', [$start, $end])

        ->selectRaw('
            COALESCE(branches.id, 0) as branch_id,
            COALESCE(branches.name, "Head Office") as branch_name,
            branches.location,

            COUNT(DISTINCT sales.id) as total_sales,

            COALESCE(SUM(sales.total), 0) as gross_total,
            COALESCE(SUM(sales.discount), 0) as total_discount,

            COALESCE(SUM(sp.paid), 0) as total_paid,

            COALESCE(SUM(sales.total), 0)
            - COALESCE(SUM(sp.paid), 0) as balance_due
        ')
        ->groupBy('branches.id', 'branches.name', 'branches.location')
        ->orderByDesc('gross_total')
        ->get();
}

    /* ══════════════════════════════════════════
     |  5. ITEMS LEDGER
     ══════════════════════════════════════════ */
    public function getItemsLedger(string $from, string $to, int $businessId, ?int $productId = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);

        $salesOut = DB::table('sale_items')
            ->join('sales',    'sales.id',    '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.business_id', $businessId)
            ->whereBetween('sales.created_at', [$start, $end])
            
            
            ->when($productId, fn($q) => $q->where('products.id', $productId))
            ->selectRaw("
                sales.created_at         as date,
                'sale'                   as type,
                sales.id                 as reference_id,
                sales.reference     as reference,
                products.id              as product_id,
                products.name            as product_name,
                products.product_code,
                sale_items.quantity      as qty_out,
                0                        as qty_in,
                sale_items.unit_price,
                sale_items.unit_price   as amount
            ");

      

        $purchasesIn = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products',  'products.id',  '=', 'purchase_items.product_id')
            ->where('purchases.business_id', $businessId)
            ->whereBetween('purchases.created_at', [$start, $end])
            ->when($productId, fn($q) => $q->where('products.id', $productId))
            ->selectRaw("
                purchases.created_at       as date,
                'purchase'                 as type,
                purchases.id               as reference_id,
                purchases.reference,
                products.id                as product_id,
                products.name              as product_name,
                products.product_code,
                0                          as qty_out,
                purchase_items.quantity    as qty_in,
                purchase_items.unit_price   as unit_price,
                purchase_items.unit_price  as amount
            ");

        return $salesOut
            // ->union($refundsIn)
            ->union($purchasesIn)
            ->orderBy('date')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  6. CUSTOMER BALANCES
     ══════════════════════════════════════════ */
    public function getCustomerBalances(int $businessId, ?string $search = null): \Illuminate\Support\Collection
    {
        // sales has no paid_amount/balance_due columns (those exist on
        // purchases, not sales) — actual payments live in sale_payments,
        // aggregated per customer here rather than per sale to avoid the
        // same join fan-out described in getSalesByCustomer() above.
        $invoicedTotals = DB::table('sales')
            ->where('business_id', $businessId)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(total) as total_invoiced');

        $paidTotals = DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.business_id', $businessId)
            ->groupBy('sales.customer_id')
            ->selectRaw('sales.customer_id as customer_id, SUM(sale_payments.amount) as total_paid');

        return DB::table('customers')
            ->where('customers.business_id', $businessId)
            ->leftJoinSub($invoicedTotals, 'it', 'it.customer_id', '=', 'customers.id')
            ->leftJoinSub($paidTotals, 'pt', 'pt.customer_id', '=', 'customers.id')
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('customers.name', 'like', "%{$search}%")
                   ->orWhere('customers.phone', 'like', "%{$search}%")
                   ->orWhere('customers.email', 'like', "%{$search}%");
            }))
            ->selectRaw('
                customers.id,
                customers.name,
                customers.phone,
                customers.email,
                COALESCE(it.total_invoiced, 0) as total_invoiced,
                COALESCE(pt.total_paid, 0)     as total_paid,
                COALESCE(it.total_invoiced, 0) - COALESCE(pt.total_paid, 0) as outstanding,
                0 as total_refunded
            ')
            ->orderByDesc('outstanding')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  7. SUPPLIER BALANCES
     ══════════════════════════════════════════ */
    public function getSupplierBalances(int $businessId, ?string $search = null): \Illuminate\Support\Collection
    {
        return DB::table('suppliers')
            ->where('suppliers.business_id', $businessId)
            ->leftJoinSub(
                DB::table('purchases')
                    ->where('business_id', $businessId)
                    // purchases has grand_total/balance, not total_amount/
                    // balance_due; also needed a GROUP BY to aggregate per
                    // supplier instead of collapsing to one arbitrary row.
                    ->groupBy('supplier_id')
                    ->selectRaw('supplier_id, SUM(grand_total) as total_purchased, SUM(paid_amount) as total_paid, SUM(balance) as outstanding'),
                'purchase_totals',
                'purchase_totals.supplier_id', '=', 'suppliers.id'
            )
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('suppliers.name', 'like', "%{$search}%")
                   ->orWhere('suppliers.phone', 'like', "%{$search}%")
                   ->orWhere('suppliers.email', 'like', "%{$search}%");
            }))
            ->selectRaw('
                suppliers.id,
                suppliers.name,
                suppliers.phone,
                suppliers.email,
                COALESCE(purchase_totals.total_purchased, 0) as total_purchased,
                COALESCE(purchase_totals.total_paid, 0)      as total_paid,
                COALESCE(purchase_totals.outstanding, 0)     as outstanding
            ')
            ->orderByDesc('outstanding')
            ->get();
    }
     public function getSalePayments(string $from, string $to, int $businessId, ?string $method = null, ?string $search = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);
 
        // sale_payments columns: business_id, user_id, sale_id, method, amount, reference, note
        return DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('users', 'users.id', '=', 'sale_payments.user_id')
            ->where('sale_payments.business_id', $businessId)
            ->whereBetween('sale_payments.created_at', [$start, $end])
            ->when($method, fn($q) => $q->where('sale_payments.method', $method))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('sales.reference', 'like', "%{$search}%")
                   ->orWhere('customers.name', 'like', "%{$search}%")
                   ->orWhere('sale_payments.reference', 'like', "%{$search}%");
            }))
            ->selectRaw('
                sale_payments.id,
                sale_payments.sale_id,
                sales.reference,
                COALESCE(customers.name, "Walk-in Customer") as party_name,
                customers.phone                              as party_phone,
                sale_payments.method                         as payment_method,
                sale_payments.amount,
                sale_payments.reference                      as reference,
                sale_payments.note                           as notes,
                users.name                                   as received_by,
                sale_payments.created_at
            ')
            ->orderByDesc('sale_payments.created_at')
            ->get();
    }
 
    public function getSalePaymentsSummary(string $from, string $to, int $businessId): array
    {
        [$start, $end] = $this->dateRange($from, $to);
 
        $totals = DB::table('sale_payments')
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();
 
        $byMethod = DB::table('sale_payments')
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('method as payment_method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->groupBy('method')
            ->get();
 
        return [
            'total_count'  => (int)   ($totals->total_count ?? 0),
            'total_amount' => (float) ($totals->total_amount ?? 0),
            'by_method'    => $byMethod,
        ];
    }
 
    /* ══════════════════════════════════════════
     |  9. TRANSACTIONS — PURCHASE PAYMENTS
     ══════════════════════════════════════════ */
    public function getPurchasePayments(string $from, string $to, int $businessId, ?string $method = null, ?string $search = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);
 
        // purchase_payments columns: purchase_id, user_id, amount, payment_method, payment_reference, notes, payment_date
        return DB::table('purchase_payments')
            ->join('purchases', 'purchases.id', '=', 'purchase_payments.purchase_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->leftJoin('users', 'users.id', '=', 'purchase_payments.user_id')
            ->where('purchases.business_id', $businessId)
            ->whereBetween('purchase_payments.payment_date', [$start, $end])
            ->when($method, fn($q) => $q->where('purchase_payments.payment_method', $method))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('purchases.reference', 'like', "%{$search}%")
                   ->orWhere('suppliers.name', 'like', "%{$search}%")
                   ->orWhere('purchase_payments.payment_reference', 'like', "%{$search}%");
            }))
            ->selectRaw('
                purchase_payments.id,
                purchase_payments.purchase_id,
                purchases.reference                        as purchase_ref,
                COALESCE(suppliers.name, "Unknown Supplier")  as party_name,
                suppliers.phone                               as party_phone,
                purchase_payments.payment_method,
                purchase_payments.amount,
                purchase_payments.payment_reference           as reference,
                purchase_payments.notes,
                users.name                                    as paid_by,
                purchase_payments.payment_date                as created_at
            ')
            ->orderByDesc('purchase_payments.payment_date')
            ->get();
    }
 
    public function getPurchasePaymentsSummary(string $from, string $to, int $businessId): array
    {
        [$start, $end] = $this->dateRange($from, $to);
 
        $totals = DB::table('purchase_payments')
            ->join('purchases', 'purchases.id', '=', 'purchase_payments.purchase_id')
            ->where('purchases.business_id', $businessId)
            ->whereBetween('purchase_payments.payment_date', [$start, $end])
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();
 
        $byMethod = DB::table('purchase_payments')
    ->join('purchases', 'purchases.id', '=', 'purchase_payments.purchase_id')
    ->where('purchases.business_id', $businessId)
    ->whereBetween('purchase_payments.payment_date', [$start, $end])
    ->selectRaw('purchase_payments.payment_method, COUNT(*) as count, COALESCE(SUM(purchase_payments.amount), 0) as total')
    ->groupBy('purchase_payments.payment_method')
    ->get();
 
        return [
            'total_count'  => (int)   ($totals->total_count ?? 0),
            'total_amount' => (float) ($totals->total_amount ?? 0),
            'by_method'    => $byMethod,
        ];
    }

    /* ══════════════════════════════════════════
     |  10. GENERAL SALES REPORT
     |  Flat transaction-level listing — distinct from the
     |  by-customer/by-branch grouped views above.
     ══════════════════════════════════════════ */
    public function getGeneralSalesReport(
        string $from,
        string $to,
        int $businessId,
        ?int $branchId = null,
        ?string $paymentMethod = null
    ): \Illuminate\Support\Collection {
        [$start, $end] = $this->dateRange($from, $to);

        $paymentsPerSale = DB::table('sale_payments')
            ->groupBy('sale_id')
            ->selectRaw('sale_id, SUM(amount) as paid, GROUP_CONCAT(DISTINCT method) as methods');

        $itemCounts = DB::table('sale_items')
            ->groupBy('sale_id')
            ->selectRaw('sale_id, SUM(quantity) as items_qty, COUNT(*) as items_count');

        return DB::table('sales')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
            ->leftJoin('users', 'users.id', '=', 'sales.user_id')
            ->leftJoinSub($paymentsPerSale, 'sp', 'sp.sale_id', '=', 'sales.id')
            ->leftJoinSub($itemCounts, 'ic', 'ic.sale_id', '=', 'sales.id')
            ->where('sales.business_id', $businessId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->when($paymentMethod, fn ($q) => $q->where('sales.payment_method', $paymentMethod))
            ->selectRaw('
                sales.id,
                sales.reference,
                sales.created_at as date,
                COALESCE(branches.name, "Head Office") as branch_name,
                COALESCE(customers.name, "Walk-in Customer") as customer_name,
                COALESCE(users.name, "—") as cashier_name,
                sales.payment_method,
                COALESCE(ic.items_count, 0) as items_count,
                COALESCE(ic.items_qty, 0) as items_qty,
                sales.subtotal,
                sales.discount,
                sales.tax,
                sales.total,
                COALESCE(sp.paid, 0) as paid,
                sales.total - COALESCE(sp.paid, 0) as balance,
                CASE
                    WHEN COALESCE(sp.paid, 0) <= 0 THEN "unpaid"
                    WHEN COALESCE(sp.paid, 0) >= sales.total THEN "paid"
                    ELSE "partial"
                END as payment_status
            ')
            ->orderByDesc('sales.created_at')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  11. REVENUE BY ITEM
     |  Ranked revenue contribution per product — a focused lens
     |  on the same underlying data as Items Sale Report.
     ══════════════════════════════════════════ */
    public function getRevenueByItem(string $from, string $to, int $businessId, ?int $branchId = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);

        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.business_id', $businessId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.product_code,
                COALESCE(categories.name, "Uncategorized") as category_name,
                SUM(sale_items.quantity) as qty_sold,
                SUM(sale_items.quantity * sale_items.unit_price) as revenue
            ')
            ->groupBy('products.id', 'products.name', 'products.product_code', 'categories.name')
            ->orderByDesc('revenue')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  12. ITEMS PURCHASE REPORT
     |  Mirror of Items Sale Report, for the purchase side.
     ══════════════════════════════════════════ */
    public function getItemsPurchaseReport(string $from, string $to, int $businessId, ?int $supplierId = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);

        return DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->where('purchases.business_id', $businessId)
            ->whereBetween('purchases.created_at', [$start, $end])
            ->when($supplierId, fn ($q) => $q->where('purchases.supplier_id', $supplierId))
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.product_code,
                COUNT(DISTINCT purchases.id) as purchase_count,
                SUM(purchase_items.quantity) as qty_purchased,
                AVG(purchase_items.unit_price) as avg_unit_price,
                SUM(purchase_items.quantity * purchase_items.unit_price) as total_cost
            ')
            ->groupBy('products.id', 'products.name', 'products.product_code')
            ->orderByDesc('total_cost')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  13. PROFIT REPORT
     |  Date-range profit (Close Day Report only ever covers one
     |  day) — per-item gross profit plus expenses deducted for a
     |  true net profit figure.
     ══════════════════════════════════════════ */
    public function getProfitReport(string $from, string $to, int $businessId): array
    {
        [$start, $end] = $this->dateRange($from, $to);

        $items = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.business_id', $businessId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.product_code,
                SUM(sale_items.quantity) as qty_sold,
                SUM(sale_items.quantity * sale_items.unit_price) as revenue,
                SUM(sale_items.quantity * products.buying_price) as cost,
                SUM(sale_items.quantity * sale_items.unit_price)
                    - SUM(sale_items.quantity * products.buying_price) as profit
            ')
            ->groupBy('products.id', 'products.name', 'products.product_code')
            ->orderByDesc('profit')
            ->get();

        $totalExpenses = (float) DB::table('expenses')
            ->where('business_id', $businessId)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        return [
            'items'          => $items,
            'total_expenses' => $totalExpenses,
        ];
    }

    /* ══════════════════════════════════════════
     |  14. STAFF SALES REPORT
     ══════════════════════════════════════════ */
    public function getStaffSalesReport(string $from, string $to, int $businessId, ?int $branchId = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);

        $paymentsPerSale = DB::table('sale_payments')
            ->groupBy('sale_id')
            ->selectRaw('sale_id, SUM(amount) as paid');

        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->leftJoinSub($paymentsPerSale, 'sp', 'sp.sale_id', '=', 'sales.id')
            ->where('sales.business_id', $businessId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->selectRaw('
                users.id as staff_id,
                users.name as staff_name,
                COUNT(DISTINCT sales.id) as total_sales,
                COALESCE(SUM(sales.total), 0) as gross_total,
                COALESCE(SUM(sales.discount), 0) as total_discount,
                COALESCE(SUM(sp.paid), 0) as total_paid,
                CASE WHEN COUNT(DISTINCT sales.id) > 0
                    THEN COALESCE(SUM(sales.total), 0) / COUNT(DISTINCT sales.id)
                    ELSE 0
                END as avg_sale_value
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('gross_total')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  15. GENERAL EXPENSES REPORT
     ══════════════════════════════════════════ */
    public function getExpensesReport(string $from, string $to, int $businessId, ?int $categoryId = null): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);

        return DB::table('expenses')
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->leftJoin('branches', 'branches.id', '=', 'expenses.branch_id')
            ->leftJoin('users', 'users.id', '=', 'expenses.created_by')
            ->where('expenses.business_id', $businessId)
            ->whereBetween('expenses.expense_date', [$start, $end])
            ->when($categoryId, fn ($q) => $q->where('expenses.expense_category_id', $categoryId))
            ->selectRaw('
                expenses.id,
                expenses.expense_date as date,
                COALESCE(expense_categories.name, "Uncategorized") as category_name,
                COALESCE(branches.name, "Head Office") as branch_name,
                expenses.description,
                expenses.amount,
                expenses.amount_paid,
                expenses.balance,
                expenses.payment_status,
                expenses.payment_method,
                expenses.reference,
                COALESCE(users.name, "—") as recorded_by
            ')
            ->orderByDesc('expenses.expense_date')
            ->get();
    }

    public function getExpensesByCategory(string $from, string $to, int $businessId): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($from, $to);

        return DB::table('expenses')
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->where('expenses.business_id', $businessId)
            ->whereBetween('expenses.expense_date', [$start, $end])
            ->selectRaw('
                COALESCE(expense_categories.name, "Uncategorized") as category_name,
                COUNT(*) as count,
                SUM(expenses.amount) as total
            ')
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->get();
    }

    /* ══════════════════════════════════════════
     |  16. ACCOUNT STATEMENT (per customer or supplier)
     |  Chronological debit/credit ledger with a running balance
     |  — the drill-down behind the Customer/Supplier Balances
     |  summary rows above.
     ══════════════════════════════════════════ */
    public function getAccountStatement(
        int $businessId,
        string $partyType,
        int $partyId,
        ?string $from = null,
        ?string $to = null
    ): array {
        $party = $partyType === 'supplier'
            ? DB::table('suppliers')->where('business_id', $businessId)->where('id', $partyId)->first()
            : DB::table('customers')->where('business_id', $businessId)->where('id', $partyId)->first();

        if (! $party) {
            return ['party' => null, 'entries' => [], 'opening_balance' => 0, 'closing_balance' => 0];
        }

        $range = $from && $to ? $this->dateRange($from, $to) : null;

        if ($partyType === 'supplier') {
            $debits = DB::table('purchases')
                ->where('business_id', $businessId)
                ->where('supplier_id', $partyId)
                ->when($range, fn ($q) => $q->whereBetween('created_at', $range))
                ->selectRaw('created_at as date, "purchase" as type, reference, grand_total as amount')
                ->get();

            $credits = DB::table('purchase_payments')
                ->join('purchases', 'purchases.id', '=', 'purchase_payments.purchase_id')
                ->where('purchases.business_id', $businessId)
                ->where('purchases.supplier_id', $partyId)
                ->when($range, fn ($q) => $q->whereBetween('purchase_payments.payment_date', $range))
                ->selectRaw('purchase_payments.payment_date as date, "payment" as type, purchase_payments.payment_reference as reference, purchase_payments.amount')
                ->get();
        } else {
            $debits = DB::table('sales')
                ->where('business_id', $businessId)
                ->where('customer_id', $partyId)
                ->when($range, fn ($q) => $q->whereBetween('created_at', $range))
                ->selectRaw('created_at as date, "sale" as type, reference, total as amount')
                ->get();

            $credits = DB::table('sale_payments')
                ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
                ->where('sales.business_id', $businessId)
                ->where('sales.customer_id', $partyId)
                ->when($range, fn ($q) => $q->whereBetween('sale_payments.created_at', $range))
                ->selectRaw('sale_payments.created_at as date, "payment" as type, sale_payments.reference, sale_payments.amount')
                ->get();
        }

        // Opening balance: everything before the range start, so the
        // statement's running balance is accurate even when a date
        // filter is applied rather than always starting at zero.
        $openingBalance = 0.0;
        if ($range) {
            $priorDebits = $partyType === 'supplier'
                ? DB::table('purchases')->where('business_id', $businessId)->where('supplier_id', $partyId)->where('created_at', '<', $range[0])->sum('grand_total')
                : DB::table('sales')->where('business_id', $businessId)->where('customer_id', $partyId)->where('created_at', '<', $range[0])->sum('total');

            $priorCredits = $partyType === 'supplier'
                ? DB::table('purchase_payments')->join('purchases', 'purchases.id', '=', 'purchase_payments.purchase_id')->where('purchases.business_id', $businessId)->where('purchases.supplier_id', $partyId)->where('purchase_payments.payment_date', '<', $range[0])->sum('purchase_payments.amount')
                : DB::table('sale_payments')->join('sales', 'sales.id', '=', 'sale_payments.sale_id')->where('sales.business_id', $businessId)->where('sales.customer_id', $partyId)->where('sale_payments.created_at', '<', $range[0])->sum('sale_payments.amount');

            $openingBalance = (float) $priorDebits - (float) $priorCredits;
        }

        $entries = $debits->map(fn ($d) => ['date' => $d->date, 'type' => $d->type, 'reference' => $d->reference, 'debit' => (float) $d->amount, 'credit' => 0])
            ->concat($credits->map(fn ($c) => ['date' => $c->date, 'type' => $c->type, 'reference' => $c->reference, 'debit' => 0, 'credit' => (float) $c->amount]))
            ->sortBy('date')
            ->values();

        $running = $openingBalance;
        $entries = $entries->map(function ($e) use (&$running) {
            $running += $e['debit'] - $e['credit'];
            $e['balance'] = $running;
            return $e;
        });

        return [
            'party'            => $party,
            'entries'          => $entries,
            'opening_balance'  => $openingBalance,
            'closing_balance'  => $running,
        ];
    }
}

