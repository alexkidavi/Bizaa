<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\ImportProductsRequest;
use App\Http\Requests\ProductEditRequest;
use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HandlesDeleteErrors;
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        return response()->json($this->service->getAllProducts());
    }

    public function show($id): JsonResponse
    {
        $product = $this->service->getProduct($id);
        $this->authorize('view', $product);
        return response()->json($product);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $user = Auth::user();
        $data = array_merge($request->validated(), [
            'user_id'     => $user->id,
            'business_id' => $user->business_id,
        ]);

        $product = $this->service->createProduct($data);
        return response()->json($product, 201);
    }

    public function updateProduct(ProductEditRequest $request, $id): JsonResponse
    {
        $product = $this->service->getProduct($id);
        $this->authorize('update', $product);
        $updated = $this->service->updateProduct($product->id, $request->validated());
        return response()->json($updated);
    }

    public function destroy($id): JsonResponse
    {
        $product = $this->service->getProduct($id);
        $this->authorize('delete', $product);
        return $this->safeDelete(
            fn () => $this->service->deleteProduct($product->id),
            'Product deleted successfully'
        );
    }

    public function importTemplate()
    {
        $this->authorize('create', Product::class);
        return $this->service->downloadTemplate();
    }

    public function import(ImportProductsRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $branchId = (int) $request->validated()['branch_id'];

        // Runs synchronously (no queue worker is set up in this environment) —
        // give it more headroom than the default request timeout. Row count is
        // still capped in ProductsImport so this can't run away indefinitely.
        set_time_limit(180);

        return response()->json(
            $this->service->importProducts($request->file('file'), $branchId)
        );
    }
}
