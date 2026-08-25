<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BusinessSubscription;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->business_id) {
            return $next($request); // unauthenticated handled elsewhere
        }

        $sub = BusinessSubscription::activeForBusiness($user->business_id)->first();

        if (!$sub) {
            // Look up latest expired sub so the frontend can show context
            $latest = BusinessSubscription::where('business_id', $user->business_id)
                ->with('plan:id,name,slug,billing_period,price')
                ->orderByDesc('ends_at')
                ->first();

            return response()->json([
                'subscription_required' => true,
                'message'               => 'Your subscription has expired. Please renew to continue.',
                'expired_at'            => $latest?->ends_at,
                'last_plan'             => $latest?->plan?->only('name', 'slug', 'billing_period', 'price'),
            ], 402);
        }
        // $request->merge(['_subscription' => $sub]);

        return $next($request);
    }
}
