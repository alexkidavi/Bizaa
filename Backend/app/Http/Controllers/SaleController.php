<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\RecordSalePaymentRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Jobs\SendSaleReceiptEmailJob;
use App\Models\Sale;
use App\Services\SaleReceiptService;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    // GET /sales/{id}/receipt-pdf — the app's own "Print" button downloads
    // this and hands it to the OS print dialog (no server-side printer
    // integration needed; works with whatever printer the device has).
    public function receiptPdf($id, SaleReceiptService $receipts): Response
    {
        $sale = $this->loadSaleForReceipt($id);
        $this->authorize('view', $sale);

        $pdf = $receipts->renderPdf($sale);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receipt-' . $sale->reference . '.pdf"',
        ]);
    }

    // POST /sales/{id}/email-receipt — "Send" button. `email` is optional;
    // defaults to the sale's own customer's email if one was selected.
    // Queued, same as every other outbound email in this app — the
    // response only confirms "handed to the queue", not "delivered".
    public function emailReceipt(Request $request, $id): JsonResponse
    {
        $sale = $this->loadSaleForReceipt($id);
        $this->authorize('view', $sale);

        $validated = $request->validate([
            'email' => 'nullable|email|max:150',
        ]);

        $email = $validated['email'] ?? $sale->customer?->email;

        if (! $email) {
            return response()->json([
                'message' => 'No email address to send to — this sale has no customer email on file. Enter one to send the receipt.',
            ], 422);
        }

        $businessName = $sale->business?->business_name ?? 'Bizaa';

        try {
            SendSaleReceiptEmailJob::dispatch($sale, $email, $businessName);
            Log::info('SaleReceiptMail: job dispatched', ['sale_id' => $sale->id, 'email' => $email]);
        } catch (\Throwable $e) {
            Log::error('SaleReceiptMail: dispatch failed', ['sale_id' => $sale->id, 'email' => $email, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Could not queue the receipt email. Please try again.'], 500);
        }

        return response()->json(['message' => "Receipt is being sent to {$email}."]);
    }

    // Both new endpoints take the same plain integer id the client already
    // holds right after creating the sale (POST /sales returns the raw
    // model, un-encrypted) — deliberately not the encrypted id getSale()
    // expects, to avoid a pointless extra encrypt/decrypt round trip for
    // an action that happens in the same flow as the sale's creation.
    private function loadSaleForReceipt($id): Sale
    {
        return Sale::with(['items.product', 'payments', 'branch', 'customer', 'cashier', 'business'])
            ->where('business_id', Auth::user()->business_id)
            ->findOrFail($id);
    }
}
