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

    return DB::table('sales')
        ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')

        // payments aggregated per sale first
        ->leftJoin('sale_payments', 'sale_payments.sale_id', '=', 'sales.id')

        ->where('sales.business_id', $businessId)
        ->whereBetween('sales.created_at', [$start, $end])
    

        ->selectRaw('
            COALESCE(customers.id, 0) as customer_id,
            COALESCE(customers.name, "Walk-in Customer") as customer_name,
            customers.phone,

            COUNT(DISTINCT sales.id) as total_sales,

            COALESCE(SUM(sales.total), 0) as gross_total,
            COALESCE(SUM(sales.discount), 0) as total_discount,

            COALESCE(SUM(sale_payments.amount), 0) as total_paid,

            COALESCE(SUM(sales.total), 0) - COALESCE(SUM(sale_payments.amount), 0) as balance_due
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

    return DB::table('sales')
        ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
        ->leftJoin('sale_payments', 'sale_payments.sale_id', '=', 'sales.id')

        ->where('sales.business_id', $businessId)
        ->whereBetween('sales.created_at', [$start, $end])

        ->selectRaw('
            COALESCE(branches.id, 0) as branch_id,
            COALESCE(branches.name, "Head Office") as branch_name,
            branches.location,

            COUNT(DISTINCT sales.id) as total_sales,

            COALESCE(SUM(sales.total), 0) as gross_total,
            COALESCE(SUM(sales.discount), 0) as total_discount,

            COALESCE(SUM(sale_payments.amount), 0) as total_paid,

            COALESCE(SUM(sales.total), 0)
            - COALESCE(SUM(sale_payments.amount), 0) as balance_due
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
        return DB::table('customers')
            ->where('customers.business_id', $businessId)
            ->leftJoinSub(
                DB::table('sales')
                    ->where('business_id', $businessId)
                    ->selectRaw('
                        customer_id,
                        SUM(paid_amount)  as total_paid,
                        SUM(balance_due)  as outstanding,
                  
                    '),
                'sale_totals',
                'sale_totals.customer_id', '=', 'customers.id'
            )
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
                COALESCE(sale_totals.total_invoiced, 0)  as total_invoiced,
                COALESCE(sale_totals.total_paid, 0)      as total_paid,
                COALESCE(sale_totals.outstanding, 0)     as outstanding,
                COALESCE(sale_totals.total_refunded, 0)  as total_refunded
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
                    ->selectRaw('supplier_id, SUM(total_amount) as total_purchased, SUM(paid_amount) as total_paid, SUM(balance_due) as outstanding'),
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
}

