<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use App\Services\MpesaService;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
class SubscriptionController extends Controller
{
    // GET /api/subscription/plans — public list of available plans
      public function allplans(): JsonResponse
    {
        $plans = SubscriptionPlan::active()
            // ->whereNotIn('slug', ['trial'])
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'slug'           => $p->slug,
                'billing_period' => $p->billing_period,
                'price'          => (float) $p->price,
                'duration_days'  => $p->duration_days,
                'features'       => $p->features ?? [],
            ]);

        return response()->json(['data' => $plans]);
    }
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::active()
            ->whereNotIn('slug', ['trial'])
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'slug'           => $p->slug,
                'billing_period' => $p->billing_period,
                'price'          => (float) $p->price,
                'duration_days'  => $p->duration_days,
                'features'       => $p->features ?? [],
            ]);

        return response()->json(['data' => $plans]);
    }

    // GET /api/subscription/current — current subscription for authenticated business
    public function current(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->business_id) {
            return response()->json(['data' => null]);
        }

        $sub = BusinessSubscription::where('business_id', $user->business_id)
            ->with('plan:id,name,slug,billing_period,price,features')
            ->orderByDesc('ends_at')
            ->first();

        if (!$sub) {
            return response()->json(['data' => null, 'is_active' => false]);
        }

        return response()->json([
            'data' => [
                'id'                => $sub->id,
                // 'status'            => $sub->status,
                'is_active'         => $sub->isActive(),
                'days_remaining'    => $sub->daysRemaining(),
                // 'starts_at'         => $sub->starts_at,
                // 'ends_at'           => $sub->ends_at,
                // 'amount_paid'       => (float) $sub->amount_paid,
                // 'payment_method'    => $sub->payment_method,
                // 'payment_reference' => $sub->payment_reference,
                // 'plan'              => $sub->plan ? [
                //     'id'             => $sub->plan->id,
                //     'name'           => $sub->plan->name,
                //     'slug'           => $sub->plan->slug,
                //     'billing_period' => $sub->plan->billing_period,
                //     'price'          => (float) $sub->plan->price,
                //     'features'       => $sub->plan->features ?? [],
                // ] : null,
            ],
            'is_active' => $sub->isActive(),
        ]);
    }

    // GET /api/subscription/history — all subscriptions for this business
    public function history(): JsonResponse
    {
        $user = Auth::user();

        $subs = BusinessSubscription::where('business_id', $user->business_id)
            ->with('plan:id,name,slug,billing_period,price')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'                => $s->id,
                'status'            => $s->status,
                'is_active'         => $s->isActive(),
                'starts_at'         => $s->starts_at,
                'ends_at'           => $s->ends_at,
                'amount_paid'       => (float) $s->amount_paid,
                'payment_method'    => $s->payment_method,
                'payment_reference' => $s->payment_reference,
                'plan_name'         => $s->plan?->name,
                'plan_slug'         => $s->plan?->slug,
            ]);

        return response()->json(['data' => $subs]);
    }

    // POST /api/subscription/initiate-stk — trigger M-Pesa STK push for a plan
    public function initiateStk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'phone'   => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        if ($plan->slug === 'trial') {
            return response()->json(['message' => 'Cannot subscribe to the trial plan.'], 422);
        }

        // Normalise phone to 254XXXXXXXXX
        $phone = preg_replace('/\D/', '', $validated['phone']);
        if (strlen($phone) === 9) {
            $phone = '254' . $phone;
        } elseif (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }

        $mpesa = app(MpesaService::class);

        try {
            $checkoutRequestId = $mpesa->initiateSubscriptionStkPush([
                'phone'       => $phone,
                'amount'      => (int) $plan->price,
                'plan_id'     => $plan->id,
                'plan_slug'   => $plan->slug,
                'business_id' => $user->business_id,
            ]);
        } catch (\Throwable $e) {
            info('Subscription STK error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Could not initiate M-Pesa payment. Please try again.'], 502);
        }

        return response()->json([
            'message'              => 'M-Pesa prompt sent. Please check your phone.',
            'checkout_request_id'  => $checkoutRequestId,
            'amount'               => $plan->price,
            'plan_name'            => $plan->name,
        ]);
    }

public function offlinePayment(Request $request): JsonResponse
{
    $validated = $request->validate([
        'plan_id' => [
            'required',
            'integer',
            'exists:subscription_plans,id',
        ],
        'confirmation_message' => [
            'required',
            'string',
            'min:10',
            'max:5000',
        ],
    ]);

    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated. Please log in again.',
        ], 401);
    }

    $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

    // Extract a possible M-Pesa transaction reference.
    // This is only a candidate until independently verified.
    $message = trim($validated['confirmation_message']);

    preg_match(
        '/^\s*([A-Z0-9]{10})\b/i',
        $message,
        $matches
    );

    $transactionCode = isset($matches[1])
        ? strtoupper($matches[1])
        : null;

    // Reject duplicate references where one can be extracted.
    if ($transactionCode && SubscriptionPayment::where(
        'transaction_code',
        $transactionCode
    )->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'This M-Pesa transaction reference has already been submitted.',
        ], 422);
    }

    // Extract the amount for preliminary review only.
    preg_match(
        '/\bKsh\.?\s*([\d,]+(?:\.\d{1,2})?)/i',
        $message,
        $amountMatches
    );

    $reportedAmount = isset($amountMatches[1])
        ? (float) str_replace(',', '', $amountMatches[1])
        : null;

    if ($reportedAmount !== null &&
        abs($reportedAmount - (float) $plan->price) > 0.001) {
        return response()->json([
            'success' => false,
            'message' => 'The amount in your confirmation message does not match the selected plan. Please check the plan and message.',
        ], 422);
    }

    $result = DB::transaction(function () use (
        $user,
        $plan,
        $message,
        $transactionCode,
        $reportedAmount
    ) {
         $plann = SubscriptionPlan::findOrFail($plan->id);
        // Create a subscription pending payment verification.
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
            'starts_at' => now(),
            'ends_at' =>now()->addDays($plann->duration_days),
        ]);

        // Save the submitted confirmation for review.
        $payment = SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'amount' => $reportedAmount ?? $plan->price,
            'transaction_code' => $transactionCode,
            'confirmation_message' => $message,
            'payment_method' => 'mpesa_offline',
            'status' => 'pending',
        ]);

        return [
            'subscription' => $subscription,
            'payment' => $payment,
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Your payment confirmation has been submitted successfully. Your subscription will be activated after payment verification.',
        'data' => [
            'subscription_id' => $result['subscription']->id,
            'payment_id' => $result['payment']->id,
            'payment_status' => 'pending',
        ],
    ], 201);
}
}
