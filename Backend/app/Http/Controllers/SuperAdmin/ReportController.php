<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\EcommerceStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function overview(): JsonResponse
    {
        // Revenue by plan (last 12 months)
        $revenueByPlan = BusinessSubscription::with('plan')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select('plan_id', DB::raw('SUM(amount_paid) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('plan_id')
            ->get()
            ->map(fn($r) => [
                'plan'    => $r->plan?->name ?? 'Unknown',
                'revenue' => (float) $r->total,
                'count'   => $r->count,
            ]);

        // Monthly revenue (last 12 months)
        $monthlyRevenue = collect(range(11, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            $revenue = BusinessSubscription::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount_paid');
            return [
                'month'   => $month->format('M Y'),
                'revenue' => (float) $revenue,
            ];
        })->values();

        // Businesses by industry
        $byIndustry = Business::select('business_industry', DB::raw('count(*) as count'))
            ->groupBy('business_industry')
            ->orderByDesc('count')
            ->get()
            ->map(fn($r) => [
                'industry' => $r->business_industry ?? 'Other',
                'count'    => $r->count,
            ]);

        // Top stores by listed products
        $topStores = EcommerceStore::with('business')
            ->withCount(['branch as listed_count' => fn($q) =>
                $q->join('product_branch', 'product_branch.branch_id', '=', 'branches.id')
                  ->where('product_branch.is_listed', true)
            ])
            ->where('is_active', true)
            ->orderByDesc('listed_count')
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'store'         => $s->name,
                'business'      => $s->business?->business_name,
                'category'      => $s->category,
                'listed_products' => $s->listed_count,
            ]);

        // Subscription status breakdown
        $subStatus = BusinessSubscription::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'revenue_by_plan'  => $revenueByPlan,
            'monthly_revenue'  => $monthlyRevenue,
            'by_industry'      => $byIndustry,
            'top_stores'       => $topStores,
            'subscription_breakdown' => $subStatus,
        ]);
    }
}
