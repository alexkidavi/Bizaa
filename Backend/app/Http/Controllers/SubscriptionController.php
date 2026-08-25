<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use App\Services\MpesaService;
use Illuminate\Support\Facades\Crypt;

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
}
