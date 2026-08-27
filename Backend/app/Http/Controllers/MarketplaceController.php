<?php

namespace App\Http\Controllers;

use App\Models\EcommerceStore;
use App\Models\ProductBranch;
use Illuminate\Http\JsonResponse;

class MarketplaceController extends Controller
{
    /**
     * List all active stores with their listed-product counts.
     */
    public function stores(): JsonResponse
    {
        $stores = EcommerceStore::where('is_active', true)
            ->with('branch')
            ->withCount([
                'branch as listed_products_count' => function ($q) {
                    $q->join('product_branch', 'branches.id', '=', 'product_branch.branch_id')
                      ->where('product_branch.is_listed', true);
                },
            ])
            ->latest()
            ->get()
            ->map(fn ($s) => $this->storeShape($s));

        return response()->json(['data' => $stores]);
    }

    /**
     * Single store by slug, including all listed products.
     */
    public function storeBySlug(string $slug): JsonResponse
    {
        $store = EcommerceStore::where('slug', $slug)
            ->where('is_active', true)
            ->with('branch')
            ->firstOrFail();

        $products = ProductBranch::where('branch_id', $store->branch_id)
            ->where('is_listed', true)
            ->with(['product.category', 'product.brand'])
            ->get()
            ->map(fn ($pb) => $this->productShape($pb, $store));

        return response()->json([
            'data' => array_merge($this->storeShape($store), ['products' => $products]),
        ]);
    }

    /**
     * Single listed product by product_branch id, with related products and store info.
     */
    public function product(int $id): JsonResponse
    {
        $pb = ProductBranch::where('id', $id)
            ->where('is_listed', true)
            ->with(['product.category', 'product.brand', 'product.unit', 'branch'])
            ->firstOrFail();

        $store = EcommerceStore::where('branch_id', $pb->branch_id)
            ->where('is_active', true)
            ->first();

        $related = [];
        if ($pb->product?->category_id) {
            $related = ProductBranch::where('branch_id', $pb->branch_id)
                ->where('is_listed', true)
                ->where('id', '!=', $id)
                ->whereHas('product', fn ($q) => $q->where('category_id', $pb->product->category_id))
                ->with(['product.category', 'product.brand'])
                ->limit(4)
                ->get()
                ->map(fn ($r) => $this->productShape($r, $store));
        }

        return response()->json([
            'data' => array_merge(
                $this->productShape($pb, $store, true),
                ['related' => $related]
            ),
        ]);
    }

    /* ── Private helpers ─────────────────────────────────── */

    private function storeShape(EcommerceStore $s, bool $withProducts = false): array
    {
        return [
            'id'                   => $s->id,
            'name'                 => $s->name,
            'slug'                 => $s->slug,
            'tagline'              => $s->tagline,
            'description'          => $s->description,
            'banner_color'         => $s->banner_color ?? '#1e3a5f',
            'category'             => $s->category,
            'location'             => $s->branch?->location ?? $s->branch?->name ?? null,
            'phone'                => $s->phone,
            'email'                => $s->email,
            'hours'                => $s->hours,
            'is_featured'          => (bool) $s->is_featured,
            'listed_products_count'=> $s->listed_products_count ?? 0,
        ];
    }

    private function productShape(ProductBranch $pb, ?EcommerceStore $store, bool $full = false): array
    {
        $p = $pb->product;

        $shape = [
            'id'                => $pb->id,
            'product_id'        => $pb->product_id,
            'name'              => $p?->name,
            'product_code'      => $p?->product_code,
            'image'             => $p?->image,
            'color'             => $p?->color,
            'category'          => $p?->category?->name,
            'category_id'       => $p?->category_id,
            'brand'             => $p?->brand?->name,
            'selling_price'     => (float) ($pb->selling_price ?? $p?->selling_price ?? 0),
            'stock'             => (float) $pb->stock,
            'short_description' => $p?->short_description,
            'store_name'        => $store?->name,
            'store_slug'        => $store?->slug,
        ];

        if ($full) {
            $shape['description']    = $p?->description;
            $shape['weight']         = $p?->weight;
            $shape['tags']           = $p?->tags ?? [];
            $shape['buying_price']   = (float) ($pb->buying_price ?? $p?->buying_price ?? 0);
            $shape['store']          = $store ? $this->storeShape($store) : null;
        }

        return $shape;
    }
}
