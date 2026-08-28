<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\LoyaltyService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    public function __construct(protected LoyaltyService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);
        return response()->json($this->service->listCustomersWithBalances($request->only(['search', 'per_page'])));
    }

    public function history(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::where('id', $customerId)->where('business_id', Auth::user()->business_id)->firstOrFail();
        $this->authorize('view', $customer);

        return response()->json([
            'customer' => $customer->only(['id', 'name', 'phone', 'email', 'loyalty_points_balance']),
            'history' => $this->service->history($customerId, (int) $request->get('per_page', 20)),
        ]);
    }

    public function adjust(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::where('id', $customerId)->where('business_id', Auth::user()->business_id)->firstOrFail();
        $this->authorize('update', $customer);

        $data = $request->validate([
            'points' => 'required|integer|not_in:0',
            'note' => 'required|string|max:255',
        ]);

        $transaction = $this->service->adjust($customerId, $data['points'], $data['note']);
        return response()->json([
            'message' => 'Points adjusted.',
            'data' => $transaction->load(['creator:id,name']),
            'balance' => $transaction->balance_after,
        ]);
    }

    /**
     * POS calls this before charging to show "customer has N points
     * (~KES X)" and whether it's enough to cover the amount they want to
     * pay with points — a pure check, no mutation.
     */
    public function previewRedeem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
        ]);

        return response()->json($this->service->previewRedeem($data['customer_id'], (float) $data['amount']));
    }
}
