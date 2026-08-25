<?php

namespace App\Repositories;

use App\Models\EcommerceStore;
use App\Models\ProductBranch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EcommerceStoreRepository
{
    public function all()
    {
        $businessId = Auth::user()->business_id;

        return EcommerceStore::where('business_id', $businessId)
            ->with(['branch', 'user'])
            ->latest()
            ->get();
    }

    public function find($id)
    {
        $businessId = Auth::user()->business_id;

        return EcommerceStore::where('business_id', $businessId)
            ->with(['branch', 'user'])
            ->findOrFail($id);
    }

    public function create(array $data): EcommerceStore
    {
        $data['slug'] = $this->uniqueSlug($data['name'], $data['slug'] ?? null);

        return EcommerceStore::create($data);
    }

    public function update($id, array $data): EcommerceStore
    {
        $store = $this->find($id);

        if (isset($data['slug']) && $data['slug'] !== $store->slug) {
            $data['slug'] = $this->uniqueSlug($data['name'] ?? $store->name, $data['slug'], $id);
        }

        $store->update($data);

        return $store->fresh(['branch', 'user']);
    }

    public function delete($id): void
    {
        $this->find($id)->delete();
    }

    /**
     * Get all products belonging to the store's branch, with their listing status.
     */
    public function getStoreProducts($storeId): array
    {
        $store = $this->find($storeId);

        $rows = ProductBranch::where('branch_id', $store->branch_id)
            ->with(['product.category', 'product.brand', 'product.unit'])
            ->get();

        return $rows->map(function ($pb) {
            return [
                'product_branch_id' => $pb->id,
                'product_id'        => $pb->product_id,
                'branch_id'         => $pb->branch_id,
                'name'              => $pb->product->name ?? null,
                'product_code'      => $pb->product->product_code ?? null,
                'image'             => $pb->product->image ?? null,
                'category'          => $pb->product->category->name ?? null,
                'brand'             => $pb->product->brand->name ?? null,
                'selling_price'     => $pb->selling_price ?? $pb->product->selling_price ?? 0,
                'buying_price'      => $pb->buying_price ?? $pb->product->buying_price ?? 0,
                'stock'             => $pb->stock,
                'is_listed'         => (bool) $pb->is_listed,
                'short_description' => $pb->product->short_description ?? null,
                'description'       => $pb->product->description ?? null,
                'tags'              => $pb->product->tags ?? [],
                'weight'            => $pb->product->weight ?? null,
            ];
        })->values()->toArray();
    }

    /**
     * Toggle the is_listed flag for a product in this store's branch.
     */
    public function toggleProductListing($storeId, $productBranchId, bool $isListed): ProductBranch
    {
        $store = $this->find($storeId);

        $pb = ProductBranch::where('id', $productBranchId)
            ->where('branch_id', $store->branch_id)
            ->firstOrFail();

        $pb->update(['is_listed' => $isListed]);

        return $pb;
    }

    /**
     * Bulk toggle listings.
     */
    public function bulkToggleListing($storeId, array $productBranchIds, bool $isListed): int
    {
        $store = $this->find($storeId);

        return ProductBranch::whereIn('id', $productBranchIds)
            ->where('branch_id', $store->branch_id)
            ->update(['is_listed' => $isListed]);
    }

    private function uniqueSlug(string $name, ?string $base = null, $exceptId = null): string
    {
        $slug = Str::slug($base ?? $name);
        $original = $slug;
        $i = 1;

        while (true) {
            $query = EcommerceStore::where('slug', $slug);
            if ($exceptId) {
                $query->where('id', '!=', $exceptId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}
