<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Services\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportsService $service) {}

    private function businessId(Request $request): int
    {
        return $request->user()->business_id;
    }

    /* ── Shared: resolve preset or custom range ── */
    private function range(Request $request): array
    {
        $request->validate([
            'preset' => 'required|in:today,this_week,this_month,custom',
            'from'   => 'required_if:preset,custom|date',
            'to'     => 'required_if:preset,custom|date|after_or_equal:from',
        ]);

        return $this->service->resolveDateRange(
            $request->preset,
            $request->from,
            $request->to,
        );
    }

    /* ── 1. Close Day Report ─────────────────── */
    public function closeDayReport(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $data = $this->service->closeDayReport(
            $request->from,
            $request->to,
            $this->businessId($request)
        );

        return response()->json(['data' => $data]);
    }

    /* ── 2. Items Sale Report ────────────────── */
    public function itemsSaleReport(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $request->validate(['branch_id' => 'nullable|integer|exists:branches,id']);

        $data = $this->service->itemsSaleReport(
            $from, $to,
            $this->businessId($request),
            $request->branch_id
        );

        return response()->json(['data' => $data]);
    }

    /* ── 3. Sales by Customer ────────────────── */
    public function salesByCustomer(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $data = $this->service->salesByCustomer(
            $from, $to,
            $this->businessId($request)
        );

        return response()->json(['data' => $data]);
    }

    /* ── 4. Sales by Branch ──────────────────── */
    public function salesByBranch(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $data = $this->service->salesByBranch(
            $from, $to,
            $this->businessId($request)
        );

        return response()->json(['data' => $data]);
    }

    /* ── 5. Items Ledger ─────────────────────── */
    public function itemsLedger(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $request->validate(['product_id' => 'nullable|integer|exists:products,id']);

        $data = $this->service->itemsLedger(
            $from, $to,
            $this->businessId($request),
            $request->product_id
        );

        return response()->json(['data' => $data]);
    }

    /* ── 6. Customer Balances ────────────────── */
    public function customerBalances(Request $request): JsonResponse
    {
        $data = $this->service->customerBalances(
            $this->businessId($request),
            $request->search
        );

        return response()->json(['data' => $data]);
    }

    /* ── 7. Supplier Balances ────────────────── */
    public function supplierBalances(Request $request): JsonResponse
    {
        $data = $this->service->supplierBalances(
            $this->businessId($request),
            $request->search
        );

        return response()->json(['data' => $data]);
    }
    public function salePayments(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);
 
        $request->validate([
            'method' => 'nullable|string',
            'search' => 'nullable|string',
        ]);
 
        $data = $this->service->salePayments(
            $from, $to,
            $this->businessId($request),
            $request->method,
            $request->search
        );
 
        return response()->json(['data' => $data]);
    }
 
    /* ── 9. Purchase Payments ────────────────── */
    public function purchasePayments(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);
 
        $request->validate([
            'method' => 'nullable|string',
            'search' => 'nullable|string',
        ]);
 
        $data = $this->service->purchasePayments(
            $from, $to,
            $this->businessId($request),
            $request->method,
            $request->search
        );
 
        return response()->json(['data' => $data]);
    }
}
