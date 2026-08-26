<?php
namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class MpesaController extends Controller
{
    protected $service;

    public function __construct(MpesaService $service)
    {
        $this->service = $service;
    }

    public function initiateStkPush(Request $request)
    {
        try {
            $checkoutRequestId = $this->service->initiateStkPush($request->all());

            return response()->json([
                'message' => 'STK push sent.',
                'checkout_request_id' => $checkoutRequestId,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
   public function stkCallback(Request $request)
{
    // Logged as a summary, not the full payload — CallbackMetadata carries
    // the payer's phone number and M-Pesa receipt number, which don't
    // belong in the general application log.
    $stk = $request->input('Body.stkCallback', []);
    info('M-PESA CALLBACK RECEIVED', [
        'MerchantRequestID' => $stk['MerchantRequestID'] ?? null,
        'CheckoutRequestID' => $stk['CheckoutRequestID'] ?? null,
        'ResultCode'        => $stk['ResultCode'] ?? null,
        'ResultDesc'        => $stk['ResultDesc'] ?? null,
    ]);
    // 🔹 Always acknowledge Safaricom FIRST (important)
    $response = response()->json([
        'ResultCode' => 0,
        'ResultDesc' => 'Accepted',
    ]);

    try {
        $this->service->handleStkCallback($request->all());
    } catch (\Throwable $e) {
        info('M-PESA CALLBACK ERROR: ' . $e->getMessage());
    }
    return $response;
}
public function checkStatus(Request $request)
{
    $request->validate([
        'checkout_request_id' => 'required|string',
    ]);

    $status = $this->service->checkStkStatus(
        $request->checkout_request_id,
        Auth::user()->business_id
    );

    return response()->json($status);
}

public function recentPayments(Request $request)
{
    $minutes = (int) $request->query('minutes', 240);

    return response()->json(
        $this->service->getRecentPayments(Auth::user()->business_id, $minutes)
    );
}
}