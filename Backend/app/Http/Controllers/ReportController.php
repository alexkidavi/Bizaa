<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Services\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;

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

    /* ── 10. General Sales Report ────────────── */
    public function generalSalesReport(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $request->validate([
            'branch_id'      => 'nullable|integer|exists:branches,id',
            'payment_method' => 'nullable|string',
        ]);

        $data = $this->service->generalSalesReport(
            $from, $to,
            $this->businessId($request),
            $request->branch_id,
            $request->payment_method
        );

        return response()->json(['data' => $data]);
    }

    /* ── 11. Revenue by Item ─────────────────── */
    public function revenueByItem(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $request->validate(['branch_id' => 'nullable|integer|exists:branches,id']);

        $data = $this->service->revenueByItem(
            $from, $to,
            $this->businessId($request),
            $request->branch_id
        );

        return response()->json(['data' => $data]);
    }

    /* ── 12. Items Purchase Report ───────────── */
    public function itemsPurchaseReport(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $request->validate(['supplier_id' => 'nullable|integer|exists:suppliers,id']);

        $data = $this->service->itemsPurchaseReport(
            $from, $to,
            $this->businessId($request),
            $request->supplier_id
        );

        return response()->json(['data' => $data]);
    }

    /* ── 13. Profit Report ───────────────────── */
    public function profitReport(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $data = $this->service->profitReport(
            $from, $to,
            $this->businessId($request)
        );

        return response()->json(['data' => $data]);
    }

    /* ── 14. Staff Sales Report ──────────────── */
    public function staffSalesReport(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $request->validate(['branch_id' => 'nullable|integer|exists:branches,id']);

        $data = $this->service->staffSalesReport(
            $from, $to,
            $this->businessId($request),
            $request->branch_id
        );

        return response()->json(['data' => $data]);
    }

    /* ── 15. General Expenses Report ─────────── */
    public function expensesReport(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        $request->validate(['category_id' => 'nullable|integer|exists:expense_categories,id']);

        $data = $this->service->expensesReport(
            $from, $to,
            $this->businessId($request),
            $request->category_id
        );

        return response()->json(['data' => $data]);
    }

    /* ── 16. Account Statement ───────────────── */
    public function accountStatement(Request $request): JsonResponse
    {
        $request->validate([
            'party_type' => 'required|in:customer,supplier',
            // Suppliers are listed with a plain integer id; customers are
            // listed with an encrypted id (CustomerRepository::all()) — an
            // existing inconsistency elsewhere in the app, not something
            // introduced here, but this endpoint has to accept whichever
            // one the party type actually uses.
            'party_id'   => 'required|string',
            'from'       => 'nullable|date',
            'to'         => 'nullable|date|after_or_equal:from',
        ]);

        $partyId = $request->party_type === 'customer'
            ? (int) Crypt::decryptString($request->party_id)
            : (int) $request->party_id;

        $data = $this->service->accountStatement(
            $this->businessId($request),
            $request->party_type,
            $partyId,
            $request->from,
            $request->to
        );

        return response()->json(['data' => $data]);
    }
}
