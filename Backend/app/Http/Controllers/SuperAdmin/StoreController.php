<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceStore;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EcommerceStore::with('business')
            ->withCount(['branch as listed_products_count' => fn($q) =>
                $q->join('product_branch', 'product_branch.branch_id', '=', 'branches.id')
                  ->where('product_branch.is_listed', true)
            ])
            ->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('is_active', $status === 'active');
        }

        $stores = $query->paginate(20);

        return response()->json($stores->through(fn($s) => [
            'id'             => $s->id,
            'name'           => $s->name,
            'slug'           => $s->slug,
            'category'       => $s->category,
            'banner_color'   => $s->banner_color,
            'is_active'      => $s->is_active,
            'is_featured'    => $s->is_featured,
            'location'       => $s->location,
            'business_id'    => $s->business_id,
            'business_name'  => $s->business?->business_name,
            'listed_products'=> $s->listed_products_count ?? 0,
            'created_at'     => $s->created_at?->format('Y-m-d'),
        ]));
    }

    public function toggleFeatured(int $id): JsonResponse
    {
        $store = EcommerceStore::findOrFail($id);
        $store->update(['is_featured' => !$store->is_featured]);

        return response()->json([
            'message'     => $store->is_featured ? 'Store featured.' : 'Store unfeatured.',
            'is_featured' => $store->is_featured,
        ]);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $store = EcommerceStore::findOrFail($id);
        $store->update(['is_active' => !$store->is_active]);

        return response()->json([
            'message'   => $store->is_active ? 'Store activated.' : 'Store deactivated.',
            'is_active' => $store->is_active,
        ]);
    }
}
