<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardRepository
{
    private ?Carbon $customStart = null;
    private ?Carbon $customEnd = null;

    public function setCustomRange(?string $startDate, ?string $endDate): void
    {
        $this->customStart = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->customEnd   = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
    }

    /* ── HELPERS ─────────────────────────── */
public function dateRange($filter)
{
    $filter = strtolower($filter);

    switch ($filter) {
        case 'custom':
            if ($this->customStart && $this->customEnd) {
                return [$this->customStart->copy(), $this->customEnd->copy()];
            }
            return [now()->startOfDay(), now()->endOfDay()];

        case 'today':
            return [now()->startOfDay(), now()->endOfDay()];

        case 'yesterday':
            return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];

        case 'this_week':
        case 'week':
            return [now()->startOfWeek(), now()->endOfWeek()];

        case 'this_month':
        case 'month':
            return [now()->startOfMonth(), now()->endOfMonth()];

        case 'this_year':
        case 'year':
            return [now()->startOfYear(), now()->endOfYear()];

        default:
            return [now()->startOfDay(), now()->endOfDay()];
    }
}

    private function previousRange(string $filter): array
    {
        if (strtolower($filter) === 'custom' && $this->customStart && $this->customEnd) {
            $days = $this->customStart->diffInDays($this->customEnd) + 1;
            return [
                $this->customStart->copy()->subDays($days),
                $this->customStart->copy()->subDay()->endOfDay(),
            ];
        }

        return match ($filter) {
            'week'  => [Carbon::now()->subWeek()->startOfWeek(),   Carbon::now()->subWeek()->endOfWeek()],
            'month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'year'  => [Carbon::now()->subYear()->startOfYear(),   Carbon::now()->subYear()->endOfYear()],
            default => [Carbon::yesterday()->startOfDay(),         Carbon::yesterday()->endOfDay()],
        };
    }

    private function chartLabelsAndGrouping(string $filter): array
    {
        if (strtolower($filter) === 'custom' && $this->customStart && $this->customEnd) {
            $days = $this->customStart->diffInDays($this->customEnd) + 1;
            if ($days <= 1)  return ['%H:00',  24, 'hour'];
            if ($days <= 90) return ['%d %b',  $days, 'day'];
            return ['%b %Y', 12, 'month'];
        }

        return match ($filter) {
            'week'  => ['%a',    7,  'day'],
            'month' => ['%d %b', 30, 'day'],
            'year'  => ['%b',    12, 'month'],
            default => ['%H:00', 24, 'hour'],
        };
    }

    // Apply optional branch filter to a query builder
    private function applyBranch($query, ?int $branchId): mixed
    {
        if ($branchId) {
            $query->where('sales.branch_id', $branchId);
        }
        return $query;
    }

    /* ── 1. STATS ────────────────────────── */
public function getStats(string $filter, int $businessId, ?int $branchId = null): array
{
    [$start, $end]         = $this->dateRange($filter);
    [$prevStart, $prevEnd] = $this->previousRange($filter);
    
    $paymentsJoin = DB::table('sale_payments')
        ->selectRaw('sale_id, SUM(amount) as total_paid')
        ->groupBy('sale_id');

    /* ── CURRENT ── */
    $current = DB::table('sales as s')
        ->leftJoinSub($paymentsJoin, 'sp', 'sp.sale_id', '=', 's.id')
        ->where('s.business_id', $businessId)
        ->when($branchId, fn($q) => $q->where('s.branch_id', $branchId))
        ->whereBetween('s.created_at', [$start, $end])
        ->whereNull('s.deleted_at')
        ->selectRaw('
            COALESCE(SUM(s.total), 0)             as total_revenue,
            COALESCE(SUM(s.discount), 0)          as total_discounts,
            COALESCE(SUM(sp.total_paid), 0)       as total_paid,
            COUNT(DISTINCT s.customer_id)         as customers,
            COUNT(*)                              as total_sales
        ')
        ->first();

    /* ── PREVIOUS ── */
    $previous = DB::table('sales as s')
        ->leftJoinSub($paymentsJoin, 'sp', 'sp.sale_id', '=', 's.id')
        ->where('s.business_id', $businessId)
        ->when($branchId, fn($q) => $q->where('s.branch_id', $branchId))
        ->whereBetween('s.created_at', [$prevStart, $prevEnd])
        ->whereNull('s.deleted_at')
        ->selectRaw('
            COALESCE(SUM(s.total), 0)         as total_revenue,
            COUNT(DISTINCT s.customer_id)     as customers
        ')
        ->first();

    /* ── COGS ── */
    $cogsQuery = fn($s, $e) => (float) DB::table('sale_items as si')
        ->join('sales as sl',    'sl.id', '=', 'si.sale_id')
        ->join('products as pr', 'pr.id', '=', 'si.product_id')
        ->where('sl.business_id', $businessId)
        ->when($branchId, fn($q) => $q->where('sl.branch_id', $branchId))
        ->whereBetween('sl.created_at', [$s, $e])
        ->sum(DB::raw('si.quantity * pr.buying_price'));

    $cogs     = $cogsQuery($start, $end);
    $prevCogs = $cogsQuery($prevStart, $prevEnd);

    /* ── TODAY vs YESTERDAY ── */
    $salesBase = fn() => DB::table('sales')
        ->where('business_id', $businessId)
        ->whereNull('deleted_at')
        ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

    $todayRevenue     = $salesBase()->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])->sum('total');
    $yesterdayRevenue = $salesBase()->whereBetween('created_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()])->sum('total');

    $grossProfit = (float) $current->total_revenue - $cogs;
    $prevProfit  = (float) $previous->total_revenue - $prevCogs;

    $stockValue = (float) DB::table('product_branch as pb')
        ->join('products as p', 'p.id', '=', 'pb.product_id')
        ->where('p.business_id', $businessId)
        ->whereNull('p.deleted_at')
        ->when($branchId, fn($q) => $q->where('pb.branch_id', $branchId))
        ->sum(DB::raw('pb.stock * pb.selling_price'));

    /* ── EXPENSES ── */
    $expensesQuery = fn($s, $e) => DB::table('expenses')
        ->where('business_id', $businessId)
        // ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
        ->whereBetween('created_at', [$s, $e])
        ->whereNull('deleted_at');

    $currentExpenses = (float) $expensesQuery($start, $end)->sum('amount');
    $prevExpenses    = (float) $expensesQuery($prevStart, $prevEnd)->sum('amount');

    /* ── PURCHASES ── */
    $purchasePaymentsJoin = DB::table('purchase_payments')
        ->selectRaw('purchase_id, SUM(amount) as total_paid')
        ->groupBy('purchase_id');

    $purchasesQuery = fn($s, $e) => DB::table('purchases as p')
        ->leftJoinSub($purchasePaymentsJoin, 'pp', 'pp.purchase_id', '=', 'p.id')
        ->where('p.business_id', $businessId)
        ->when($branchId, fn($q) => $q->whereIn('p.id', function ($sub) use ($branchId) {
            $sub->select('purchase_id')->from('purchase_branch')->where('branch_id', $branchId);
        }))
        ->whereBetween('p.created_at', [$s, $e])
        ->whereNull('p.deleted_at')
        ->selectRaw('
            COALESCE(SUM(p.grand_total), 0) as total_purchases,
            COALESCE(SUM(pp.total_paid), 0) as paid_amount,
            COALESCE(SUM(p.grand_total), 0) - COALESCE(SUM(pp.total_paid), 0) as pending_amount
        ')
        ->first();

    $currentPurchases = $purchasesQuery($start, $end);
    $prevPurchases    = $purchasesQuery($prevStart, $prevEnd);

    /* ── NET PROFIT (Revenue - COGS - Expenses - Purchases) ── */
    $netProfit = $grossProfit - $currentExpenses - (float) $currentPurchases->total_purchases;
    $prevNetProfit = $prevProfit - $prevExpenses - (float) $prevPurchases->total_purchases;

    return [
        'total_sales'    => (float) $current->total_revenue,
        'total_paid'     => (float) $current->total_paid,
        'today_sales'    => (float) $todayRevenue,
        'customers'      => (int) $current->customers,
        'profit'         => $grossProfit,
        'stock_value'    => $stockValue,
        
        /* ── EXPENSES ── */
        'expenses' => [
            'total' => $currentExpenses,
            'by_category' => $this->getExpensesByCategory($start, $end, $businessId, $branchId),
        ],
        
        /* ── PURCHASES ── */
        'purchases' => [
            'total_purchases' => (float) $currentPurchases->total_purchases,
            'paid_amount'     => (float) $currentPurchases->paid_amount,
            'pending_amount'  => (float) $currentPurchases->pending_amount,
            'payment_status'  => $this->getPurchasePaymentStatus($currentPurchases),
        ],
        
        /* ── NET PROFIT ── */
        'net_profit' => (float)($current->total_revenue-($currentExpenses+$currentPurchases->paid_amount)),

        'trends' => [
            'total_sales' => $this->trend((float) $previous->total_revenue, (float) $current->total_revenue),
            'today_sales' => $this->trend((float) $yesterdayRevenue, (float) $todayRevenue),
            'customers'   => $this->trend((float) $previous->customers, (float) $current->customers),
            'profit'      => $this->trend($prevProfit, $grossProfit),
            'expenses'    => $this->trend($prevExpenses, $currentExpenses),
            'purchases'   => $this->trend((float) $prevPurchases->total_purchases, (float) $currentPurchases->total_purchases),
            'net_profit'  => $this->trend($prevNetProfit, $netProfit),
        ],
    ];
}

/* ── HELPER METHODS ── */

private function getExpensesByCategory($start, $end, int $businessId, ?int $branchId): array
{
    $expensesByCategory = DB::table('expenses')
        ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
        ->where('expenses.business_id', $businessId)
        ->when($branchId, fn($q) => $q->where('expenses.branch_id', $branchId))
        ->whereBetween('expenses.created_at', [$start, $end])
        ->whereNull('expenses.deleted_at')
        ->selectRaw('
            expense_categories.name as category,
            COALESCE(SUM(expenses.amount), 0) as total
        ')
        ->groupBy('expense_categories.id', 'expense_categories.name')
        ->get()
        ->toArray();

    return $expensesByCategory;
}

private function getPurchasePaymentStatus($purchases): string
{
    $total = (float) $purchases->total_purchases;
    $paid  = (float) $purchases->paid_amount;
    
    if ($total == 0) return 'no_purchases';
    if ($paid == 0) return 'unpaid';
    if ($paid >= $total) return 'paid';
    if ($paid < $total) return 'partial';
    
    return 'unknown';
}
    private function trend(float $prev, float $current): float
    {
        if ($prev == 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $prev) / $prev) * 100, 1);
    }

    /* ── 2. CHART DATA ───────────────────── */
    public function getChartData(string $filter, int $businessId, ?int $branchId = null): array
    {
        [$start, $end]                   = $this->dateRange($filter);
        [$mysqlFmt, $points, $groupUnit] = $this->chartLabelsAndGrouping($filter);

        $salesRows = DB::table('sales')
            ->where('business_id', $businessId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(created_at, '{$mysqlFmt}') as period, COALESCE(SUM(total), 0) as revenue")
            ->groupByRaw("DATE_FORMAT(created_at, '{$mysqlFmt}')")
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $profitRows = DB::table('sale_items')
            ->join('sales',    'sales.id',    '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.business_id', $businessId)
            ->when($branchId, fn($q) => $q->where('sales.branch_id', $branchId))
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw("
                DATE_FORMAT(sales.created_at, '{$mysqlFmt}') as period,
                COALESCE(SUM(sale_items.unit_price), 0)
                - COALESCE(SUM(sale_items.quantity * products.buying_price), 0) as profit
            ")
            ->groupByRaw("DATE_FORMAT(sales.created_at, '{$mysqlFmt}')")
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $payments = DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.business_id', $businessId)
            ->when($branchId, fn($q) => $q->where('sales.branch_id', $branchId))
            ->whereBetween('sale_payments.created_at', [$start, $end])
            ->selectRaw('sale_payments.method as payment_method, COALESCE(SUM(sale_payments.amount), 0) as total')
            ->groupBy('sale_payments.method')
            ->get();

        $totalPayments = $payments->sum('total') ?: 1;
        $paymentBreakdown = $payments->map(fn($p) => [
            'label'  => ucfirst($p->payment_method),
            'total'  => (float) $p->total,
            'pct'    => round(($p->total / $totalPayments) * 100, 1),
            'method' => $p->payment_method,
        ])->values();

        $labels = $salesRows->keys()->merge($profitRows->keys())->unique()->sort()->values();

        return [
            'labels'   => $labels->values(),
            'sales'    => $labels->map(fn($l) => (float) ($salesRows[$l]->revenue ?? 0))->values(),
            'profit'   => $labels->map(fn($l) => (float) ($profitRows[$l]->profit ?? 0))->values(),
            'payments' => $paymentBreakdown,
        ];
    }

    /* ── 3. TOP PRODUCTS ─────────────────── */
    public function getTopProducts(
    string $filter,
    int $businessId,
    ?int $branchId = null,
    int $limit = 10
): \Illuminate\Support\Collection {
    [$start, $end] = $this->dateRange($filter);

    $items = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->join('products', 'products.id', '=', 'sale_items.product_id')
        ->where('sales.business_id', $businessId)
        ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
        ->whereBetween('sales.created_at', [$start, $end])
        ->selectRaw('
            products.id,
            products.name,
            SUM(sale_items.quantity) AS units,
            SUM(sale_items.quantity * sale_items.unit_price) AS revenue
        ')
        ->groupBy('products.id', 'products.name')
        ->orderByDesc('revenue')
        ->limit($limit)
        ->get();

    $maxRevenue = $items->max('revenue') ?: 1;

    return $items->map(fn ($p) => [
        'name'    => $p->name,
        'units'   => (int) $p->units,
        'revenue' => (float) $p->revenue,
        'pct'     => round(($p->revenue / $maxRevenue) * 100),
    ]);
}

    /* ── 4. RECENT TRANSACTIONS ──────────── */
    public function getRecentTransactions(int $businessId, ?int $branchId = null, int $limit = 5): \Illuminate\Support\Collection
    {
        return DB::table('sales')
            ->leftJoin('customers',     'customers.id',         '=', 'sales.customer_id')
            ->leftJoin('sale_payments', 'sale_payments.sale_id','=', 'sales.id')
            ->where('sales.business_id', $businessId)
            ->when($branchId, fn($q) => $q->where('sales.branch_id', $branchId))
            ->selectRaw('
                sales.id,
                sales.reference                                    as receipt,
                COALESCE(customers.name, "Walk-in Customer")       as customer,
                sales.total                                        as amount,
                sale_payments.method                               as method,
                sales.created_at
            ')
            ->orderByDesc('sales.created_at')
            ->limit($limit)
            ->get()
            ->map(fn($tx) => [
                'receipt'  => $tx->receipt ?? '—',
                'customer' => $tx->customer,
                'amount'   => (float) $tx->amount,
                'method'   => ucfirst($tx->method ?? 'cash'),
                'time'     => Carbon::parse($tx->created_at)->diffForHumans(),
            ]);
    }

    /* ── 5. LOW STOCK ────────────────────── */
    public function getLowStock(int $businessId, ?int $branchId = null, int $threshold = 10): \Illuminate\Support\Collection
    {
        return DB::table('products')
            ->leftJoin('categories',    'categories.id',           '=', 'products.category_id')
            ->leftJoin('product_branch','product_branch.product_id','=', 'products.id')
            ->where('products.business_id', $businessId)
            ->when($branchId, fn($q) => $q->where('product_branch.branch_id', $branchId))
            ->groupBy('products.id', 'products.name', 'products.reorder_level', 'categories.name')
            ->havingRaw(
                'GREATEST(COALESCE(SUM(product_branch.stock), 0), 0) <= COALESCE(products.reorder_level, ?)',
                [$threshold]
            )
            ->selectRaw('
                products.name,
                GREATEST(COALESCE(SUM(product_branch.stock), 0), 0) as stock,
                COALESCE(products.reorder_level, ?)                  as reorder_level,
                COALESCE(categories.name, "Uncategorized")           as category
            ', [$threshold])
            ->orderBy('stock')
            ->limit(8)
            ->get();
    }

    /* ── 6. TODAY SUMMARY ────────────────── */
    public function getTodaySummary(int $businessId, ?int $branchId = null): array
    {
        $start = Carbon::today()->startOfDay();
        $end   = Carbon::today()->endOfDay();

        $paymentsSub = DB::raw('(
            SELECT sale_id, SUM(amount) as total_paid
            FROM sale_payments
            GROUP BY sale_id
        ) as sp');

        $row = DB::table('sales as s')
            ->leftJoin($paymentsSub, 'sp.sale_id', '=', 's.id')
            ->where('s.business_id', $businessId)
            ->when($branchId, fn($q) => $q->where('s.branch_id', $branchId))
            ->whereBetween('s.created_at', [$start, $end])
            ->selectRaw('
                COUNT(*)                                       as total_transactions,
                COALESCE(SUM(s.total), 0)                      as total_revenue,
                COALESCE(SUM(sp.total_paid), 0)                as paid,
                COALESCE(SUM(s.total) - SUM(sp.total_paid), 0) as balance,
                COALESCE(SUM(s.discount), 0)                   as discounts,
                CASE WHEN COUNT(*) > 0
                     THEN COALESCE(SUM(s.total), 0) / COUNT(*)
                     ELSE 0
                END                                            as avg_sale
            ')
            ->first();

        return [
            'total_transactions' => (int)   $row->total_transactions,
            'total_revenue'      => (float) $row->total_revenue,
            'paid'               => (float) $row->paid,
            'balance'            => (float) $row->balance,
            'discounts'          => (float) $row->discounts,
            'avg_sale'           => (float) $row->avg_sale,
        ];
    }

    /* ── 7. OVERALL OUTSTANDING BALANCE ──── */
    public function getOverallOutstanding(int $businessId, ?int $branchId = null): float
    {
        $paymentsSub = DB::raw('(
            SELECT sale_id, SUM(amount) as total_paid
            FROM sale_payments
            GROUP BY sale_id
        ) as sp');

        $val = DB::table('sales as s')
            ->leftJoin($paymentsSub, 'sp.sale_id', '=', 's.id')
            ->where('s.business_id', $businessId)
            ->when($branchId, fn($q) => $q->where('s.branch_id', $branchId))
            ->selectRaw('COALESCE(SUM(s.total), 0) - COALESCE(SUM(sp.total_paid), 0) as outstanding')
            ->value('outstanding');

        return max(0.0, (float) ($val ?? 0));
    }

    /* ── 8. TOP BRANCHES ─────────────────── */
    public function getTopBranches(string $filter, int $businessId, int $limit = 6): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange($filter);

        $paymentsSub = DB::raw('(
            SELECT sale_id, SUM(amount) as total_paid
            FROM sale_payments
            GROUP BY sale_id
        ) as sp');

        $rows = DB::table('sales as s')
            ->leftJoin($paymentsSub, 'sp.sale_id', '=', 's.id')
            ->join('branches', 'branches.id', '=', 's.branch_id')
            ->where('s.business_id', $businessId)
            ->whereBetween('s.created_at', [$start, $end])
            ->selectRaw('
                branches.id,
                branches.name,
                COUNT(*)                           as sales_count,
                COALESCE(SUM(s.total), 0)          as total_sales,
                COALESCE(SUM(sp.total_paid), 0)    as revenue
            ')
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();

        return $rows->map(fn($b) => [
            'id'          => $b->id,
            'name'        => $b->name,
            'sales_count' => (int)   $b->sales_count,
            'total_sales' => (float) $b->total_sales,
            'revenue'     => (float) $b->revenue,
        ]);
    }
}