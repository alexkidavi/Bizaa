<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductBranch;
use App\Models\StockTakeItem;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        return response()->json($this->service->getAllStock());
    }
    // Get stock by product
    public function getByProduct($product): JsonResponse
    {
        return response()->json(
            $this->service->getByProduct($product)
        );
    }

    // Adjust stock (add/remove)
    public function adjustStock(Request $request, $product): JsonResponse
    {
        $this->service->adjust($product, $request->all());

        return response()->json(
            $this->service->getByProduct($product)
        );
    }

    // Create / Register stock
    public function stockTake(Request $request): JsonResponse
    {
        // info($request->all());
        $stock = $this->service->stockTake($request->all());

        return response()->json([
            'message' => 'Stock created successfully',
            'data' => $stock,
        ], 201);
    }

    // Update stock
    public function updateCategory(Request $request, $id): JsonResponse
    {
        $stock = $this->service->update($id, $request->all());

        return response()->json([
            'message' => 'Stock updated successfully',
            'data' => $stock
        ]);
    }

    // Delete stock
    public function destroy($id): JsonResponse
    {
        $id = Crypt::decryptString($id);
        $this->service->delete($id);

        return response()->json([
            'message' => 'Stock deleted successfully'
        ]);
    }
    public function show($id): JsonResponse
{
    $id = Crypt::decryptString($id);
    return response()->json(
        $this->service->getStockTakeById($id)
    );
}
public function getItems(Request $request, $id): JsonResponse
{
    $id = Crypt::decryptString($id);
    return response()->json(
        $this->service->getStockTakeItems($request, $id)
    );
}
public function saveCounts(Request $request, $id)
{
    $id = Crypt::decryptString($id);
    info($request->all());
    foreach ($request->items as $item) {
        StockTakeItem::where('id', $item['item_id'])->update([
            'counted_stock' => $item['counted_stock'],
        ]);
    }

    return response()->json([
        'message' => 'Counts saved successfully'
    ]);
}
public function transferStock(Request $request)
{
    return response()->json(
        $this->service->transferStock($request->all())
    );

}
public function getTransfers()
{
    return response()->json(
        $this->service->getTransfers()
    );

}
public function branchProducts(Request $request)
{
    $branchId = $request->get('branch_id');
    $search   = $request->get('search', '');
    $perPage  = $request->get('per_page', 10);

    return ProductBranch::query()
        ->join('products as p', 'p.id', '=', 'product_branch.product_id')
        ->where('product_branch.branch_id', $branchId)
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