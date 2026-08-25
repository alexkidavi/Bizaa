<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\RecordSalePaymentRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    use HandlesDeleteErrors;

    public function __construct(protected SaleService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->getAllSales($request->all()));
    }

    public function createPosSale(StoreSaleRequest $request): JsonResponse
    {
        return response()->json($this->service->createPosSale($request->validated()));
    }

    public function getById($sale): JsonResponse
    {
        $saleModel = $this->service->getSale($sale);
        $this->authorize('view', $saleModel);

        return response()->json($saleModel);
    }

    public function getByProduct($id): JsonResponse
    {
        return response()->json($this->service->getSaleByProduct($id));
    }

    public function recordPayment(RecordSalePaymentRequest $request, $sale): JsonResponse
    {
        $data = $request->validated();
        $data['business_id'] = Auth::user()->business_id;
        $data['user_id'] = Auth::user()->id;

        return response()->json($this->service->recordPayment($sale, $data));
    }

    public function destroy($id): JsonResponse
    {
        return $this->safeDelete(function () use ($id) {
            $sale = Sale::findOrFail($id);
            $this->authorize('delete', $sale);
            $sale->delete();
        }, 'Sale deleted successfully');
    }
}
