<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\SaveStockCountsRequest;
use App\Http\Requests\StockTakeRequest;
use App\Http\Requests\TransferStockRequest;
use App\Models\ProductBranch;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class StockController extends Controller
{
    protected $service;

    public function __construct(StockService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        // Scoped to the caller's business inside StockRepository::getAllStock().
        return response()->json($this->service->getAllStock());
    }

    // Get stock by product
    public function getByProduct($product): JsonResponse
    {
        // Scoped to the caller's business inside StockRepository::getByProduct().
        return response()->json(
            $this->service->getByProduct($product)
        );
    }

    // Adjust stock (add/remove)
    public function adjustStock(AdjustStockRequest $request, $product): JsonResponse
    {
        $this->service->adjust($product, $request->validated());

        return response()->json(
            $this->service->getByProduct($product)
        );
    }

    // Create / Register stock take
    public function stockTake(StockTakeRequest $request): JsonResponse
    {
        $stock = $this->service->stockTake($request->validated());

        return response()->json([
            'message' => 'Stock created successfully',
            'data' => $stock,
        ], 201);
    }

    // Update stock (ProductBranch quantity)
    public function updateCategory(Request $request, $id): JsonResponse
    {
        $stock = $this->service->update($id, $request->all());

        return response()->json([
            'message' => 'Stock updated successfully',
            'data' => $stock
        ]);
    }

    // Delete stock take
    public function destroy($id): JsonResponse
    {
        $id = Crypt::decryptString($id);
        $stockTake = $this->service->findStockTakeOrFail($id);
        $this->authorize('delete', $stockTake);

        $this->service->delete($id);

        return response()->json([
            'message' => 'Stock deleted successfully'
        ]);
    }

    public function show($id): JsonResponse
    {
        $id = Crypt::decryptString($id);
        // getStockTakeById() already scopes by business_id (404s cross-tenant);
        // authorize() is a second, explicit layer matching every sibling controller.
        $stockTake = $this->service->getStockTakeById($id);
        $this->authorize('view', $stockTake);

        return response()->json($stockTake);
    }

    public function getItems(Request $request, $id): JsonResponse
    {
        $id = Crypt::decryptString($id);
        $stockTake = $this->service->findStockTakeOrFail($id);
        $this->authorize('view', $stockTake);

        return response()->json(
            $this->service->getStockTakeItems($request, $id)
        );
    }

    public function saveCounts(SaveStockCountsRequest $request, $id): JsonResponse
    {
        $id = Crypt::decryptString($id);
        $stockTake = $this->service->findStockTakeOrFail($id);
        $this->authorize('update', $stockTake);

        $this->service->saveCounts($stockTake, $request->validated()['items']);

        return response()->json([
            'message' => 'Counts saved successfully'
        ]);
    }

    public function transferStock(TransferStockRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->transferStock($request->validated())
        );
    }

    public function getTransfers(): JsonResponse
    {
        // Scoped to the caller's business inside StockRepository::getTransfers().
        return response()->json(
            $this->service->getTransfers()
        );
    }

    public function branchProducts(Request $request)
    {
        $businessId = Auth::user()->business_id;

        $branchId = $request->get('branch_id');
        $search   = $request->get('search', '');
        $perPage  = $request->get('per_page', 10);

        return ProductBranch::query()
            ->join('products as p', 'p.id', '=', 'product_branch.product_id')
            ->join('branches as b', 'b.id', '=', 'product_branch.branch_id')
            ->where('product_branch.branch_id', $branchId)
            // Defense-in-depth tenant scoping: the branch_id itself could
            // belong to another business, so both sides of the pivot must
            // be confirmed to belong to the caller's business.
            ->where('b.business_id', $businessId)
            ->where('p.business_id', $businessId)
            ->where('product_branch.stock', '>', 0)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('p.name', 'like', "%{$search}%")
                        ->orWhere('p.product_code', 'like', "%{$search}%");
                });
            })
            ->select(
                'product_branch.product_id',
                'product_branch.stock',
                'p.name as name',
                'p.product_code'
            )
            ->paginate($perPage)
            ->through(fn($p) => [
                'id'              => $p->product_id,
                'name'            => $p->name,
                'product_code'    => $p->product_code,
                'available_stock' => $p->stock,
            ]);
    }
}
