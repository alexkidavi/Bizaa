<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\EcommerceStore;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // Totals
        $totalBusinesses   = Business::count();
        $totalUsers        = User::where('is_superadmin', false)->count();
        $totalStores       = EcommerceStore::count();
        $activeStores      = EcommerceStore::where('is_active', true)->count();

        // Subscriptions
        $activeSubscriptions = BusinessSubscription::whereIn('status', ['trial', 'active'])
            ->where('ends_at', '>', now())
            ->count();

        $expiredSubscriptions = BusinessSubscription::whereNotIn('status', ['trial', 'active'])
            ->orWhere('ends_at', '<=', now())
            ->count();

        $trialSubscriptions = BusinessSubscription::where('status', 'trial')
            ->where('ends_at', '>', now())
            ->count();

        // Revenue (total amount paid on active/historical subscriptions)
        $totalRevenue = BusinessSubscription::sum('amount_paid');

        $revenueThisMonth = BusinessSubscription::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount_paid');

        // Recent businesses (last 10)
        $recentBusinesses = Business::orderByDesc('created_at')
            ->take(10)
            ->get(['id', 'business_name', 'business_email', 'business_industry', 'created_at'])
            ->map(fn($b) => [
                'id'       => $b->id,
                'name'     => $b->business_name,
                'email'    => $b->business_email,
                'industry' => $b->business_industry,
                'joined'   => $b->created_at->diffForHumans(),
            ]);

        // Subscriptions expiring in the next 7 days
        $expiringSoon = BusinessSubscription::with('business')
            ->whereIn('status', ['trial', 'active'])
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', now()->addDays(7))
            ->orderBy('ends_at')
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'business' => $s->business?->business_name,
                'plan'     => $s->status,
                'ends_at'  => $s->ends_at?->format('Y-m-d'),
                'days'     => (int) now()->diffInDays($s->ends_at, false),
            ]);

        // New businesses per month (last 6 months)
        $growthChart = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            return [
                'month' => $month->format('M Y'),
                'count' => Business::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        })->values();

        return response()->json([
            'stats' => [
                'total_businesses'      => $totalBusinesses,
                'total_users'           => $totalUsers,
                'total_stores'          => $totalStores,
                'active_stores'         => $activeStores,
                'active_subscriptions'  => $activeSubscriptions,
                'expired_subscriptions' => $expiredSubscriptions,
                'trial_subscriptions'   => $trialSubscriptions,
                'total_revenue'         => (float) $totalRevenue,
                'revenue_this_month'    => (float) $revenueThisMonth,
            ],
            'recent_businesses' => $recentBusinesses,
            'expiring_soon'     => $expiringSoon,
            'growth_chart'      => $growthChart,
        ]);
    }
}
