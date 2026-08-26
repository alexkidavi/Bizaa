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
        $data = $request->validated();

        // mpesa_method 'mpesa_manual' (freely-typed code) and the
        // MANUAL-CONFIRM sentinel (cashier clicks "Confirm Paid" on a
        // pending STK push with no Safaricom confirmation ever received)
        // both declare a payment received with zero independent
        // verification. Only a supervisor override should be able to
        // push that through — checked here against the authenticated
        // user, never trusted from the request payload itself.
        $isUnverifiedMpesaClaim = ($data['mpesa_method'] ?? null) === 'mpesa_manual'
            || ($data['mpesa_code'] ?? null) === 'MANUAL-CONFIRM';

        if ($isUnverifiedMpesaClaim && ! Auth::user()->hasPermission('sales.confirm_manual_payment')) {
            return response()->json([
                'message' => 'You do not have permission to manually confirm an unverified M-Pesa payment. Ask a supervisor to confirm it.',
            ], 403);
        }

        try {
            return response()->json($this->service->createPosSale($data));
        } catch (\Exception $e) {
            // Thrown by SaleRepository::createPosSale() when a locked stock
            // row doesn't have enough quantity for this line item.
            return response()->json(['message' => $e->getMessage()], 422);
        }
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
