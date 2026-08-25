<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\StockHistory;
use App\Models\StockTake;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class StockRepository
{
    protected $model;

    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL PRODUCTS
    |--------------------------------------------------------------------------
    */
public function getAllStock()
{
    $perPage = request('per_page', 10);
    $search  = request('search');
    $user    = Auth::user();

    $query = StockTake::with(['items.branch'])
        ->where('business_id', $user->business_id)
        ->latest();

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('reference', 'like', "%{$search}%");
        });
    }

    $paginated = $query->paginate($perPage);

    $data = $paginated->getCollection()->map(function ($stock) {

        return [
            'id'          => Crypt::encryptString($stock->id),
            'business_id' => $stock->business_id,
            'user_id'     => $stock->user_id,
            'name'        => $stock->name,
            'description' => $stock->description,
            'created_by'  => $stock->created_by,
            'created_at'  => $stock->created_at,
            'updated_at'  => $stock->updated_at,
            'reference'   => $stock->reference,

            // ✅ simplified branches (only id + name)
            'branches' => $stock->items
                ->map(fn($item) => $item->branch)
                ->filter()
                ->unique('id')
                ->map(fn($branch) => [
                    'id'   => $branch->id,
                    'name' => $branch->name,
                ])
                ->values(),
        ];
    });

    return [
        'data'         => $data->values(),
        'current_page' => $paginated->currentPage(),
        'last_page'    => $paginated->lastPage(),
        'per_page'     => $paginated->perPage(),
        'total'        => $paginated->total(),
    ];
}
public function all()
{
    $perPage  = request('per_page', 10);
    $search   = request('search');
    $category = request('category');
    $branch   = request('branch');
    $stock    = request('stock');

    $query = $this->model->with([
        'category:id,name',
        'brand:id,name',
        'unit:id,name',
        'branches:id,name',
    ])->whereNull('parent_id');

    /* 🔍 SEARCH */
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /* 📂 CATEGORY FILTER */
    if ($category) {
        $query->where('category_id', $category);
    }

    /* 🏬 BRANCH FILTER */
    if ($branch) {
        $query->whereHas('branches', function ($q) use ($branch) {
            $q->where('branches.id', $branch);
        });
    }

    /* 📦 STOCK FILTER (PIVOT SAFE) */
 /* 📦 STOCK FILTER (BRANCH AWARE) */
if ($stock) {

    // =========================
    // CASE 1: branch selected
    // =========================
    if ($branch) {

        $query->whereHas('branches', function ($q) use ($stock, $branch) {

            $q->where('branches.id', $branch);

            if ($stock === 'low') {
                $q->where('product_branch.stock', '<', 10);
            }

            if ($stock === 'out') {
                $q->where('product_branch.stock', '=', 0);
            }

            if ($stock === 'ok') {
                $q->where('product_branch.stock', '>=', 10);
            }
        });
    }

    // =========================
    // CASE 2: ALL branches (FIXED)
    // =========================
    else {

        $query->whereIn('products.id', function ($sub) use ($stock) {

            $sub->select('product_branch.product_id')
                ->from('product_branch')
                ->groupBy('product_branch.product_id');

            if ($stock === 'out') {
                $sub->havingRaw('SUM(product_branch.stock) = 0');
            }

            if ($stock === 'low') {
                $sub->havingRaw('SUM(product_branch.stock) > 0 AND SUM(product_branch.stock) < 10');
            }

            if ($stock === 'ok') {
                $sub->havingRaw('SUM(product_branch.stock) >= 10');
            }
        });
    }
}

    /* 📊 CLONE QUERY FOR STATS */
    $allProducts = (clone $query)->with('branches')->get();

    $stats = [
        'total_products' => $allProducts->count(),

        'total_value' => $allProducts->sum(function ($p) use ($branch) {

            $stock = $branch
                ? $p->branches->first()?->pivot->stock ?? 0
                : $p->branches->sum(fn($b) => $b->pivot->stock);

            return $p->selling_price * $stock;
        }),

        'low_stock' => $allProducts->filter(function ($p) use ($branch) {

            $stock = $branch
                ? $p->branches->first()?->pivot->stock ?? 0
                : $p->branches->sum(fn($b) => $b->pivot->stock);

            return $stock < 10;
        })->count(),

        'categories' => $allProducts->pluck('category_id')->unique()->count(),
    ];

    /* 📄 PAGINATION */
    $products = $query->paginate($perPage);

    $products->getCollection()->transform(function ($product) use ($branch) {

        // STOCK CALCULATION (PER BRANCH SAFE)
        $stock = 0;

        if ($branch) {
            $branchData = $product->branches->firstWhere('id', $branch);
            $stock = $branchData?->pivot?->stock ?? 0;
        } else {
            $stock = $product->branches->sum(fn($b) => $b->pivot->stock);
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'product_code' => $product->product_code,
            'barcode' => $product->barcode,

            'category' => $product->category?->name ?? '_',
            'brand' => $product->brand?->name ?? '_',
            'unit' => $product->unit?->name ?? '_',

            'buying_price' => (float) $product->buying_price,
            'selling_price' => (float) $product->selling_price,

            'current_stock' => $stock,

            'branches' => $product->branches->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'stock' => $b->pivot->stock,
                ];
            })->values(),
        ];
    });

    return [
        'data' => $products->items(),
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
        'per_page' => $products->perPage(),
        'total' => $products->total(),
        'stats' => $stats,
    ];
}
    /*
    |--------------------------------------------------------------------------
    | FIND SINGLE PRODUCT
    |--------------------------------------------------------------------------
    */
public function getByProduct($id)
{
    $id=Crypt::decryptString($id);
    $branches = ProductBranch::where('product_branch.product_id', $id)
    ->join('branches', 'branches.id', '=', 'product_branch.branch_id')
    ->select(
        'branches.id as branch_id',
        'branches.name as branch_name',
        'product_branch.stock as quantity'
    )
    ->get();
    return $branches;
}

public function find($id)
{
    return $this->model->with([
        'category:id,name',
        'brand:id,name',
        'unit:id,name',
        'branches:id,name',
    ])->findOrFail($id);
}
    /*
    |--------------------------------------------------------------------------
    | CREATE PRODUCT (MAIN + VARIANTS + BRANCHES)
    |--------------------------------------------------------------------------
    */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            // 1. CREATE MAIN PRODUCT
            $product = $this->model->create([
                'name' => $data['name'],
                'business_id' => $data['business_id'],
                'user_id' => $data['user_id'],
                'product_code' => $data['product_code'],
                'barcode' => $data['barcode'] ?? null,
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'unit_id' => $data['unit_id'],

                'buying_price' => $data['buying_price'],
                'selling_price' => $data['selling_price'],
                'wholesale_price' => $data['wholesale_price'] ?? null,

                'tax_type' => $data['tax_type'] ?? 'vat',

                'opening_stock' => $data['opening_stock'] ?? 0,
                'reorder_level' => $data['reorder_level'] ?? 0,

                'for_sale' => $data['for_sale'] ?? true,
                'for_purchase' => $data['for_purchase'] ?? true,
            ]);

            // 2. ATTACH BRANCHES (PIVOT)
            if (!empty($data['branch_ids'])) {
                foreach ($data['branch_ids'] as $branchId) {
                    $product->branches()->attach($branchId, [
                        'stock' => $data['opening_stock'] ?? 0,
                        'buying_price' => $data['buying_price'],
                        'selling_price' => $data['selling_price'],
                    ]);
                }
            }

            // 3. CREATE VARIANTS
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $variant) {

                    $variantProduct = $this->model->create([
                        'business_id' => $data['business_id'],
                'user_id' => $data['user_id'],
                        'name' => $variant['name'],
                        'product_code' => $variant['code'],
                        'barcode' => $variant['barcode'] ?? null,

                        'parent_id' => $product->id,

                        'category_id' => $product->category_id,
                        'brand_id' => $product->brand_id,
                        'unit_id' => $product->unit_id,

                        'buying_price' => $variant['buying_price'],
                        'selling_price' => $variant['selling_price'],
                        'tax_type' => $product->tax_type,

                        'for_sale' => true,
                        'for_purchase' => true,
                    ]);

                    // attach same branches for variant
                    if (!empty($data['branch_ids'])) {
                        foreach ($data['branch_ids'] as $branchId) {
                            $variantProduct->branches()->attach($branchId, [
                                'stock' => $variant['stock'] ?? 0,
                                'buying_price' => $variant['buying_price'],
                                'selling_price' => $variant['selling_price'],
                            ]);
                        }
                    }
                }
            }

            return $product->load(['branches', 'variants']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PRODUCT
    |--------------------------------------------------------------------------
    */
    public function update($id, array $data)
    {
        $product = $this->model->findOrFail($id);
        $product->update($data);

        return $product;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT
    |--------------------------------------------------------------------------
    */
    public function delete($id)
    {
        $product = $this->model->findOrFail($id);
        return $product->delete();
    }


public function transferStock($data)
{
    $user = Auth::user();

    DB::beginTransaction();

    try {
        $reference = 'TRF-' . strtoupper(uniqid());

        foreach ($data['items'] as $item) {

            $productId = $item['product_id'];
            $qty       = $item['quantity'];

            /* ───────── FROM BRANCH (DEDUCT) ───────── */
            $fromRecord = ProductBranch::where('product_id', $productId)
                ->where('branch_id', $data['from_branch_id'])
                ->lockForUpdate()
                ->first();

            if (!$fromRecord) {
                throw new \Exception("Product not found in source branch");
            }

            if ($fromRecord->stock < $qty) {
                throw new \Exception("Insufficient stock for product ID {$productId}");
            }

            $fromRecord->decrement('stock', $qty);

            StockHistory::create([
                'reference'   => $reference,
                'product_id'  => $fromRecord->product_id,
                'branch_id'   => $data['from_branch_id'],
                'business_id' => $user->business_id,
                'user_id'     => $user->id,
                'type'        => 'transfer',
                'action'      => 'remove',
                'quantity'    => $qty,
                'notes'       => $data['notes'] ?? 'Stock transferred out',
            ]);

            /* ───────── TO BRANCH (ADD) ───────── */
            $toRecord = ProductBranch::firstOrCreate(
                [
                    'product_id' => $fromRecord->product_id,
                    'branch_id'  => $data['to_branch_id'],
                ],
                [
                    'buying_price'  => $fromRecord->buying_price,
                    'selling_price' => $fromRecord->selling_price,
                    'stock'         => 0,
                ]
            );

            $toRecord->increment('stock', $qty);

            StockHistory::create([
                'reference'   => $reference,
                'product_id'  => $fromRecord->product_id,
                'branch_id'   => $data['to_branch_id'],
                'business_id' => $user->business_id,
                'user_id'     => $user->id,
                'type'        => 'transfer',
                'action'      => 'add',
                'quantity'    => $qty,
                'notes'       => $data['notes'] ?? 'Stock received from transfer',
            ]);
        }

        DB::commit();

        return $reference;

    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}
public function getTransfers()
{
    $histories = StockHistory::where('type', 'transfer')
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy('reference');

    $products = \App\Models\Product::pluck('name', 'id');
    $branches = \App\Models\Branch::pluck('name', 'id');

    return $histories->map(function ($records) use ($products, $branches) {

        $from = $records->firstWhere('action', 'remove');
        $to   = $records->firstWhere('action', 'add');

        return [
            'reference' => $records->first()->reference,

            'from_branch' => $branches[$from->branch_id] ?? null,
            'to_branch'   => $branches[$to->branch_id] ?? null,

            'transfer_date' => $records->first()->created_at,
            'items_count' => $records->count(),
            'status'=>'completed',

            // 👇 all products in this transfer
            'items' => $records->map(function ($r) use ($products) {
                return [
                    'product_id' => $r->product_id,
                    'product_name' => $products[$r->product_id] ?? null,
                    'qty' => $r->quantity,
                ];
            })->values(),
        ];
    })->values();
}
}