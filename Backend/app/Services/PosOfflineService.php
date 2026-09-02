<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\LoyaltySetting;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * Builds the one-shot snapshot the POS caches in IndexedDB so it can keep
 * selling with no connection: the branch's full product catalogue (with
 * per-branch stock and price), categories, the business header for the
 * printed receipt, and a slice of recent customers.
 *
 * Read-only. The write path (queued offline sales) goes back through the
 * normal POST /sales/pos-sale with `is_offline = true`.
 */
class PosOfflineService
{
    /**
     * Hard cap on catalogue size in one snapshot. A business past this many
     * sellable products needs the paginated variant of this endpoint (not
     * built yet) — until then we return what fits and flag it truncated.
     */
    private const PRODUCT_CAP = 5000;

    public function bootstrap(int $branchId): array
    {
        $user       = Auth::user();
        $businessId = $user->business_id;

        $branch = Branch::where('business_id', $businessId)->findOrFail($branchId);

        $business = Business::find($businessId);
        $loyalty  = LoyaltySetting::where('business_id', $businessId)->first();

        $products = $this->products($businessId, $branchId);

        return [
            'synced_at' => now()->toIso8601String(),

            'branch' => [
                'id'   => $branch->id,
                'name' => $branch->name,
            ],

            // The full branch list too, so a POS that reloads with no
            // connection can still populate its branch switcher.
            'branches' => Branch::where('business_id', $businessId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])
                ->values(),

            'business' => [
                'name'     => $business?->business_name,
                'logo'     => $business?->business_logo,
                'phone'    => $business?->business_phone,
                'email'    => $business?->business_email,
                'location' => $business?->business_location,
                'kra_pin'  => $business?->kra_pin,
                'tax_rate' => (float) ($business?->tax_rate ?? 16),
            ],

            // POS.vue currently hardcodes 16% — surfaced here so the offline
            // cart math can read the same figure the receipt shows.
            'tax_rate' => (float) ($business?->tax_rate ?? 16),

            'loyalty' => [
                'is_enabled'       => (bool) ($loyalty?->is_enabled ?? false),
                'point_value'      => (float) ($loyalty?->point_value ?? 0),
                'min_redeem_points' => (int) ($loyalty?->min_redeem_points ?? 0),
            ],

            'categories' => Category::where('business_id', $businessId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
                ->values(),

            'products'           => $products['items'],
            'products_truncated' => $products['truncated'],

            // Recent customers only — enough for the common "regular walks in"
            // case. The online POS still searches the full list server-side
            // when there's a connection (CustomerService.searchForPos()).
            'customers' => Customer::where('business_id', $businessId)
                ->where('status', 'active')
                ->orderByDesc('id')
                ->limit(500)
                ->get(['id', 'name', 'phone', 'loyalty_points_balance'])
                ->map(fn ($c) => [
                    'id'    => $c->id,
                    'name'  => $c->name,
                    'phone' => $c->phone,
                    'loyalty_points_balance' => (int) $c->loyalty_points_balance,
                ])
                ->values(),
        ];
    }

    /**
     * Every sellable product at this branch, shaped exactly like the rows
     * ProductRepository::all() hands the POS today (encrypted id, float
     * price, branch-scoped stock) plus `category_id` so the cached copy can
     * be filtered by category with no round trip.
     */
    private function products(int $businessId, int $branchId): array
    {
        $query = Product::query()
            ->with(['unit:id,name'])
            ->whereNull('parent_id')
            ->where('business_id', $businessId)
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->with(['branches' => fn ($q) => $q->where('branches.id', $branchId)])
            ->orderBy('name');

        $truncated = (clone $query)->count() > self::PRODUCT_CAP;

        $items = $query->limit(self::PRODUCT_CAP)->get()->map(function ($product) {
            $pivot = $product->branches->first()?->pivot;

            return [
                'id'            => Crypt::encryptString($product->id),
                'name'          => $product->name,
                'product_code'  => $product->product_code,
                'barcode'       => $product->barcode,
                'category_id'   => $product->category_id,
                'unit'          => $product->unit?->name ?? '_',
                'selling_price' => (float) ($pivot->selling_price ?? $product->selling_price),
                'current_stock' => (float) ($pivot->stock ?? 0),
            ];
        })->values();

        return ['items' => $items, 'truncated' => $truncated];
    }
}
