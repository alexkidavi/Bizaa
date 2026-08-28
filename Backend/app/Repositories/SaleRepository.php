<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockHistory;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\LoyaltyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SaleRepository implements SaleRepositoryInterface
{
    protected $model;

    public function __construct(Product $product, protected LoyaltyService $loyalty)
    {
        $this->model = $product;
    }

    public function createPosSale($data)
    {
        return DB::transaction(function () use ($data) {
            /*
            |-----------------------------------------
            | 1. CREATE SALE (HEADER)
            |-----------------------------------------
            */
            $sale = Sale::create([
                'user_id'        => $data['user_id'],
                'business_id'    => $data['business_id'],
                'branch_id'      => $data['branch_id'],
                'payment_method' => $data['payment_method'],
                'customer_id'    => $data['customer_id'] ?? null,
                'reference'      => $data['reference'] ?? strtoupper('POS-' . uniqid()),

                'subtotal'       => $data['subtotal'],
                'discount'       => $data['discount'] ?? 0,
                'tax'            => $data['tax'] ?? 0,
                'total'          => $data['total'],
            ]);

            /*
            |-----------------------------------------
            | 2. CREATE SALE ITEMS + STOCK REDUCTION
            |-----------------------------------------
            */
            foreach ($data['items'] as $item) {
                $productId = Crypt::decryptString($item['product_id']);
                $quantity = (float) $item['quantity'];

                SaleItem::create([
                    'sale_id'     => $sale->id,
                    'product_id'  => $productId,
                    'quantity'    => $quantity,
                    'unit_price'  => $item['unit_price'],
                    'discount'    => $item['discount'] ?? 0,
                    'note'        => $item['note'] ?? null,
                ]);

                // Locked for the rest of this transaction so two concurrent
                // POS checkouts for the same product/branch can't both read
                // the same stock figure and both decrement from it —
                // matching the pattern StockRepository::transferStock()
                // already uses correctly for the equivalent operation.
                $productBranch = ProductBranch::where('product_id', $productId)
                    ->where('branch_id', $data['branch_id'])
                    ->lockForUpdate()
                    ->first();

                // No product_branch row at all means zero stock has ever
                // been provisioned for this product at this branch — that
                // used to silently skip the check (and the decrement),
                // letting it sell without limit. Treat it the same as an
                // insufficient-stock row: block the sale.
                if (!$productBranch || (float) $productBranch->stock < $quantity) {
                    throw new \Exception("Insufficient stock for product ID {$productId}");
                }

                $productBranch->decrement('stock', $quantity);

                StockHistory::create([
                    'product_id'  => $productId,
                    'branch_id'   => $data['branch_id'],
                    'business_id' => $data['business_id'],
                    'user_id'     => $data['user_id'],
                    'reference'   => $sale->reference . '-' . strtoupper(uniqid()),
                    'type'        => 'sale',
                    'action'      => 'remove',
                    'quantity'    => $quantity,
                    'notes'       => 'Sold via POS',
                ]);
            }

            /*
            |-----------------------------------------
            | 3. PAYMENT LOGIC (EXCLUDE CREDIT)
            |-----------------------------------------
            */
            $isCredit = ($data['payment_method'] ?? null) === 'credit';

            if (!$isCredit) {
                // SPLIT PAYMENTS
                if (!empty($data['payments']) && is_array($data['payments'])) {
                    foreach ($data['payments'] as $payment) {
                        // A "points" leg isn't a plain payment row — it has to
                        // check and debit the customer's points balance first
                        // (throwing, and rolling back the whole sale, if they
                        // can't cover it), so it's routed through LoyaltyService
                        // instead of a bare SalePayment::create().
                        if (($payment['method'] ?? null) === 'points') {
                            $this->loyalty->redeemForSale(
                                $data['customer_id'] ?? null,
                                (float) $payment['amount'],
                                $sale->id,
                                $data['business_id'],
                                $data['user_id']
                            );
                            continue;
                        }

                        SalePayment::create([
                            'user_id'     => $data['user_id'],
                            'business_id' => $data['business_id'],
                            'sale_id'     => $sale->id,
                            // Real M-Pesa receipt code when this leg is mpesa,
                            // otherwise a generated internal reference.
                            'reference'   => $payment['mpesa_code'] ?? strtoupper('TRF-' . uniqid()),
                            'method'      => $payment['method'],
                            'amount'      => $payment['amount'],
                            'note'        => $payment['note'] ?? null,
                        ]);
                    }
                } elseif (($data['payment_method'] ?? null) === 'points') {
                    // SINGLE PAYMENT, paid entirely with points.
                    $this->loyalty->redeemForSale(
                        $data['customer_id'] ?? null,
                        (float) ($data['paid_amount'] ?? $data['total']),
                        $sale->id,
                        $data['business_id'],
                        $data['user_id']
                    );
                } else {
                    // SINGLE PAYMENT — POS sends the M-Pesa receipt code as a
                    // top-level mpesa_code, not nested under payments[].
                    SalePayment::create([
                        'user_id'     => $data['user_id'],
                        'business_id' => $data['business_id'],
                        'sale_id'     => $sale->id,
                        'reference'   => $data['mpesa_code'] ?? strtoupper('TRF-' . uniqid()),
                        'method'      => $data['payment_method'] ?? 'cash',
                        'amount'      => $data['paid_amount'] ?? $data['total'],
                        'note'        => $data['mpesa_phone'] ?? null,
                    ]);
                }
            }

            // Registered customers earn points on the sale total regardless
            // of how it was paid (including a points-redeeming sale itself —
            // matches "points per transaction amount" literally rather than
            // trying to carve out the points-paid portion). No-op if
            // loyalty is disabled or there's no customer on this sale.
            $this->loyalty->earnForSale($sale);

            return $sale->load(['items', 'payments']);
        });
    }

    /**
     * @return array{paginator: \Illuminate\Contracts\Pagination\LengthAwarePaginator, stats: array}
     */
    public function getAllSales(array $data): array
    {
        $user = Auth::user();

        /*
        |-----------------------------------------
        | BASE QUERY (LIST)
        |-----------------------------------------
        */
        $query = Sale::with(['items', 'payments', 'customer', 'branch:id,name'])
            ->withSum('payments as paid_amount', 'amount')
            ->where('business_id', $user->business_id);

        /*
        |-----------------------------------------
        | APPLY FILTERS (REUSABLE)
        |-----------------------------------------
        */
        $applyFilters = function ($q) use ($data) {
            // Search
            if (!empty($data['search'])) {
                $search = $data['search'];

                $q->where(function ($q2) use ($search) {
                    $q2->where('reference', 'like', "%{$search}%")
                       ->orWhere('total', 'like', "%{$search}%")
                       ->orWhereHas('customer', function ($cq) use ($search) {
                           $cq->where('name', 'like', "%{$search}%");
                       });
                });
            }

            // Date range
            if (!empty($data['date_from'])) {
                $q->whereDate('created_at', '>=', $data['date_from']);
            }

            if (!empty($data['date_to'])) {
                $q->whereDate('created_at', '<=', $data['date_to']);
            }

            // Payment method
            if (!empty($data['payment_method'])) {
                $q->where('payment_method', $data['payment_method']);
            }

            return $q;
        };

        $query = $applyFilters($query);

        /*
        |-----------------------------------------
        | STATUS FILTER (DB LEVEL)
        |-----------------------------------------
        */
        if (!empty($data['status'])) {
            switch ($data['status']) {
                case 'unpaid':
                    $query->havingRaw('COALESCE(paid_amount, 0) = 0');
                    break;

                case 'paid':
                    $query->havingRaw('COALESCE(paid_amount, 0) = total');
                    break;

                case 'partial':
                    $query->havingRaw('COALESCE(paid_amount, 0) > 0 AND COALESCE(paid_amount, 0) < total');
                    break;

                case 'overpaid':
                    $query->havingRaw('COALESCE(paid_amount, 0) > total');
                    break;

                case 'credit':
                    $query->where('payment_method', 'credit');
                    break;
            }
        }

        /*
        |-----------------------------------------
        | SORTING + PAGINATION
        |-----------------------------------------
        */
        $sales = $query->orderBy(
            $data['sort_by'] ?? 'created_at',
            $data['sort_dir'] ?? 'desc'
        )->paginate($data['per_page'] ?? 20);

        /*
        |-----------------------------------------
        | STATS BASE QUERY
        |-----------------------------------------
        */
        $baseStatsQuery = Sale::query()->where('business_id', $user->business_id);
        $baseStatsQuery = $applyFilters($baseStatsQuery);

        $todayStart = Carbon::today();
        $weekStart  = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $todaySales = (clone $baseStatsQuery)->whereDate('created_at', $todayStart);
        $weekSales  = (clone $baseStatsQuery)->where('created_at', '>=', $weekStart);
        $monthSales = (clone $baseStatsQuery)->where('created_at', '>=', $monthStart);

        /*
        |-----------------------------------------
        | OUTSTANDING (WITH PAYMENTS)
        |-----------------------------------------
        */
        $allSales = (clone $baseStatsQuery)
            ->withSum('payments as paid_amount', 'amount')
            ->get();

        $outstanding = 0;
        $outstandingCount = 0;

        foreach ($allSales as $sale) {
            $paid = $sale->paid_amount ?? 0;

            if ($paid < $sale->total) {
                $outstanding += ($sale->total - $paid);
                $outstandingCount++;
            }
        }

        $stats = [
            'today' => (float) $todaySales->sum('total'),
            'todayCount' => $todaySales->count(),

            'week' => (float) $weekSales->sum('total'),
            'weekCount' => $weekSales->count(),

            'month' => (float) $monthSales->sum('total'),
            'monthCount' => $monthSales->count(),

            'outstanding' => round($outstanding, 2),
            'outstandingCount' => $outstandingCount,
        ];

        return [
            'paginator' => $sales,
            'stats'     => $stats,
        ];
    }

    public function getSale($id)
    {
        $id = Crypt::decryptString($id);

        $sale = Sale::with([
            'items.product',
            'payments',
            'branch',
            'customer',
            'cashier',
        ])
            ->where('business_id', Auth::user()->business_id)
            ->findOrFail($id);

        $sale->items->transform(function ($item) {
            $item->name = $item->product->name ?? null;
            return $item;
        });

        return $sale;
    }

    public function getSaleByProduct($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (\Exception $e) {
            abort(404);
        }

        $businessId = Auth::user()->business_id;

        $productOwnedByBusiness = Product::where('id', $id)
            ->where('business_id', $businessId)
            ->exists();

        if (!$productOwnedByBusiness) {
            abort(404);
        }

        $sales = Sale::query()
            ->select(['id', 'reference', 'branch_id', 'total', 'created_at'])
            ->with(['branch:id,name'])
            ->where('business_id', $businessId)
            ->whereHas('items', function ($query) use ($id) {
                $query->where('product_id', $id);
            })
            ->withCount(['items as items_count' => function ($query) use ($id) {
                $query->where('product_id', $id);
            }])
            ->get();

        $totalTransactions = $sales->count();

        $totalUnits = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.product_id', $id)
            ->where('sales.business_id', $businessId)
            ->sum('sale_items.quantity');

        $totalRevenue = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.product_id', $id)
            ->where('sales.business_id', $businessId)
            ->selectRaw('SUM(sale_items.quantity * sale_items.unit_price) as revenue')
            ->value('revenue');

        return [
            'sales' => $sales,
            'salesSummary' => [
                'total_transactions' => $totalTransactions,
                'total_units' => (int) $totalUnits,
                'total_revenue' => (float) $totalRevenue,
            ],
        ];
    }

    public function recordPayment($id, array $data)
    {
        $sale = Sale::where('id', $id)
            ->where('business_id', $data['business_id'])
            ->firstOrFail();

        return SalePayment::create([
            'user_id'     => $data['user_id'],
            'business_id' => $data['business_id'],
            'sale_id'     => $sale->id,
            'reference'   => $data['mpesa_code'] ?? strtoupper('TRF-' . uniqid()),
            'method'      => $data['method'] ?? 'cash',
            'amount'      => $data['amount'],
            'note'        => $data['note'] ?? null,
        ]);
    }
}
