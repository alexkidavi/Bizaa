<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
class ProductRepository
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
public function all()
{
    $user     = Auth::user();
    $perPage  = request('per_page', 10);
    $search   = request('search');
    $category = request('category');
    $category_id = request('category_id');
    // Two different callers use two different param names for the same
    // thing (Products/Items/List.vue sends `branch`, the POS sends
    // `branch_id`) — accept either so per-branch stock scoping actually
    // applies regardless of which page is asking. Without this, `$branch`
    // was always empty for POS requests, so stock always fell back to the
    // ALL-BRANCHES total instead of the selected branch's real figure.
    $branch   = request('branch') ?: request('branch_id');
    $stock    = request('stock');
    $query = $this->model->with([
        'category:id,name',
        'brand:id,name',
        'unit:id,name',
        'branches',
    ])
    ->whereNull('parent_id')
    ->where('business_id', $user->business_id)
    ->orderBy('id', 'desc');
    /* 🔍 SEARCH */
    if ($search) {
     
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    if ($category) {
   
        $query->where('category_id', $category);
    }

    if ($category_id) {
     
        $query->where('category_id', $category_id);
    }

    if ($branch) {
     
        $query->whereHas('branches', function ($q) use ($branch) {
            $q->where('branches.id', $branch);
        });
    }

    /* 📦 STOCK FILTER */
    if ($stock) {

        if ($branch) {

            $query->whereHas('branches', function ($q) use ($stock, $branch) {

                $q->where('branches.id', $branch);

                if ($stock === 'low') {
                    $q->where('product_branch.stock', '<', 10);
                } elseif ($stock === 'out') {
                    $q->where('product_branch.stock', 0);
                } elseif ($stock === 'ok') {
                    $q->where('product_branch.stock', '>=', 10);
                }
            });

        } else {

            $query->whereIn('products.id', function ($sub) use ($stock) {

                $sub->select('product_branch.product_id')
                    ->from('product_branch')
                    ->groupBy('product_branch.product_id');

                if ($stock === 'out') {
                    $sub->havingRaw('SUM(product_branch.stock) = 0');
                } elseif ($stock === 'low') {
                    $sub->havingRaw('SUM(product_branch.stock) > 0 AND SUM(product_branch.stock) < 10');
                } elseif ($stock === 'ok') {
                    $sub->havingRaw('SUM(product_branch.stock) >= 10');
                }
            });
        }
    }


    /* 📊 STATS */
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

     
        $stock = $branch
            ? $product->branches->firstWhere('id', $branch)?->pivot?->stock ?? 0
            : $product->branches->sum(fn($b) => $b->pivot->stock);

        return [
            'id' => Crypt::encryptString(($product->id)),

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
                    'id' => ($b->id),
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
public function find($id)
{
    $id   = Crypt::decryptString($id);
    $user = Auth::user();
    return $this->model->with([
        'category:id,name',
        'brand:id,name',
        'unit:id,name',
        'branches:id,name',
    ])->where('id', $id)
      ->where('business_id', $user->business_id)
      ->firstOrFail();
}
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

                'item_class_code' => $data['item_class_code'] ?? null,
                'item_type_code' => $data['item_type_code'] ?? '2',
                'tax_type_code' => $data['tax_type_code'] ?? null,
                'package_unit_code' => $data['package_unit_code'] ?? null,
                'quantity_unit_code' => $data['quantity_unit_code'] ?? null,

                'opening_stock' => $data['opening_stock'] ?? 0,
                'reorder_level' => !is_null($data['reorder_level'] ?? null)
    ? $data['reorder_level']
    : 10,

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
    $user = Auth::user();

    $product = $this->model->where('id', $id)
        ->where('business_id', $user->business_id)
        ->firstOrFail();

    $product->update([
        'name' => $data['name'],
        // 'product_code' => $data['product_code'],
        'barcode' => $data['barcode'] ?? null,

        'category_id' => $data['category_id'],
        'brand_id' => $data['brand_id'] ?? null,
        'unit_id' => $data['unit_id'],

        'buying_price' => $data['buying_price'] ,
        'selling_price' => $data['selling_price'],
        'wholesale_price' => $data['wholesale_price'] ?? null,

        'tax_type' => $data['tax_type'],

        'item_class_code' => $data['item_class_code'] ?? null,
        'item_type_code' => $data['item_type_code'] ?? '2',
        'tax_type_code' => $data['tax_type_code'] ?? null,
        'package_unit_code' => $data['package_unit_code'] ?? null,
        'quantity_unit_code' => $data['quantity_unit_code'] ?? null,

        // 'opening_stock' => $data['opening_stock'] ?? 0,

        'reorder_level' => !is_null($data['reorder_level'] ?? null)
            ? $data['reorder_level']
            : 10,

        'for_sale' => $data['for_sale'] ?? true,
        'for_purchase' => $data['for_purchase'] ?? true,
    ]);

    // update branches
if (!empty($data['branch_ids'])) {

    $branches = [];

    foreach ($data['branch_ids'] as $branchId) {
        $branches[$branchId] = [
            'buying_price' => $data['buying_price'],
            'selling_price' => $data['selling_price'],
        ];
    }

    $product->branches()->sync($branches);
}

    return $product->load(['branches', 'variants']);
}

    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT
    |--------------------------------------------------------------------------
    */
    public function delete($id)
    {
        $user    = Auth::user();
        $product = $this->model->where('id', $id)
            ->where('business_id', $user->business_id)
            ->firstOrFail();
        return $product->delete();
    }
}