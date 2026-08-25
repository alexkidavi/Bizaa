<?php

namespace App\Http\Controllers;

use App\Services\EcommerceStoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EcommerceStoreController extends Controller
{
    public function __construct(protected EcommerceStoreService $service) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getAllStores(),
        ]);
    }

    public function show($id): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getStore($id),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'   => 'required|exists:branches,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'tagline'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'banner_color'=> 'nullable|string|max:20',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:255',
            'hours'       => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $user               = Auth::user();
        $data['user_id']    = $user->id;
        $data['business_id']= $user->business_id;

        $store = $this->service->createStore($data);

        return response()->json([
            'message' => 'Ecommerce store created successfully',
            'data'    => $store->load(['branch', 'user']),
        ], 201);
    }

    public function update($id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'tagline'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'banner_color'=> 'nullable|string|max:20',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:255',
            'hours'       => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $store = $this->service->updateStore($id, $data);

        return response()->json([
            'message' => 'Store updated successfully',
            'data'    => $store,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->service->deleteStore($id);

        return response()->json(['message' => 'Store deleted successfully']);
    }

    /* ── Product Listing Management ─────────────────── */

    public function storeProducts($id): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getStoreProducts($id),
        ]);
    }

    public function toggleListing($storeId, $productBranchId, Request $request): JsonResponse
    {
        $data = $request->validate([
            'is_listed' => 'required|boolean',
        ]);

        $pb = $this->service->toggleProductListing($storeId, $productBranchId, $data['is_listed']);

        return response()->json([
            'message'   => $data['is_listed'] ? 'Product listed on store' : 'Product unlisted from store',
            'is_listed' => $pb->is_listed,
        ]);
    }

    public function bulkToggle($storeId, Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_branch_ids'   => 'required|array',
            'product_branch_ids.*' => 'integer',
            'is_listed'            => 'required|boolean',
        ]);

        $count = $this->service->bulkToggleListing($storeId, $data['product_branch_ids'], $data['is_listed']);

        return response()->json([
            'message' => $count . ' product(s) ' . ($data['is_listed'] ? 'listed' : 'unlisted'),
            'count'   => $count,
        ]);
    }
}
