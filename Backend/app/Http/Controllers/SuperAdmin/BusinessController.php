<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Business::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%");
            });
        }

        $businesses = $query->orderByDesc('created_at')->paginate(20);

        $businessIds = $businesses->pluck('id');

        // Load active subscriptions for visible businesses
        $activeSubs = BusinessSubscription::whereIn('business_id', $businessIds)
            ->whereIn('status', ['trial', 'active'])
            ->where('ends_at', '>', now())
            ->orderByDesc('ends_at')
            ->get()
            ->keyBy('business_id');

        // Onboarding/activity progress — same four "did they actually set
        // up shop" checks DashboardRepository::getOnboardingStatus() uses
        // for the business's own dashboard checklist, batch-counted here
        // (one grouped query per resource, not per-row) so super-admin
        // can see setup progress and real usage across every tenant at a
        // glance, not just whether their subscription is current.
        $unitCounts      = $this->countsByBusiness('units', $businessIds);
        $categoryCounts  = $this->countsByBusiness('categories', $businessIds);
        $productCounts   = $this->countsByBusiness('products', $businessIds);
        $saleCounts      = $this->countsByBusiness('sales', $businessIds);

        // Most recent login across every user of each business — not just
        // the owner — so a business where only a cashier has signed in
        // this week still reads as active, not dormant.
        $lastLogins = User::whereIn('business_id', $businessIds)
            ->whereNotNull('last_login_at')
            ->select('business_id', DB::raw('MAX(last_login_at) as last_login_at'))
            ->groupBy('business_id')
            ->pluck('last_login_at', 'business_id');

        $data = $businesses->through(fn($b) => [
            'id'             => $b->id,
            'name'           => $b->business_name,
            'email'          => $b->business_email,
            'phone'          => $b->business_phone,
            'location'       => $b->business_location,
            'code'           => $b->business_code,
            'created_at'     => $b->created_at?->format('Y-m-d'),
            'units_count'      => (int) ($unitCounts[$b->id] ?? 0),
            'categories_count' => (int) ($categoryCounts[$b->id] ?? 0),
            'products_count'   => (int) ($productCounts[$b->id] ?? 0),
            'sales_count'      => (int) ($saleCounts[$b->id] ?? 0),
            'last_login_at'    => isset($lastLogins[$b->id]) ? \Carbon\Carbon::parse($lastLogins[$b->id])->toIso8601String() : null,
            'subscription' => isset($activeSubs[$b->id]) ? [
                'status'   => $activeSubs[$b->id]->status,
                'ends_at'  => $activeSubs[$b->id]->ends_at?->format('Y-m-d'),
                'days_left'=> (int) now()->diffInDays($activeSubs[$b->id]->ends_at, false),
            ] : null,
        ]);

        return response()->json($data);
    }

    // Platform-wide summary cards for the top of the Businesses page.
    public function stats(): JsonResponse
    {
        // A business counts as "active" if its most recent subscription is
        // currently in force; "expired" means it has subscription history
        // but no currently-active row — mirrors BusinessSubscription::isActive().
        // A business that has never subscribed at all is neither (matches
        // the "No subscription" badge already shown in the table, which is
        // its own distinct state from "used to be subscribed, now isn't").
        $activeBusinessIds = BusinessSubscription::whereIn('status', ['trial', 'active'])
            ->where('ends_at', '>', now())
            ->distinct()
            ->pluck('business_id');

        $expiredBusinessCount = BusinessSubscription::whereNotIn('business_id', $activeBusinessIds)
            ->distinct('business_id')
            ->count('business_id');

        return response()->json([
            'total_businesses'     => Business::count(),
            'active_subscriptions' => $activeBusinessIds->count(),
            'expired_subscriptions'=> $expiredBusinessCount,
            'products_uploaded'    => Product::count(),
            'sales_made'           => Sale::count(),
        ]);
    }

    // Raw DB::table() (not the Eloquent model) so this stays one grouped
    // query regardless of which table — but that means Laravel's SoftDeletes
    // global scope doesn't apply automatically, and every one of these
    // tables (units, categories, products, sales) uses it — excluded here
    // by hand, or a voided sale / deleted product would still count as
    // "activity".
    private function countsByBusiness(string $table, $businessIds)
    {
        return DB::table($table)
            ->whereIn('business_id', $businessIds)
            ->whereNull('deleted_at')
            ->select('business_id', DB::raw('count(*) as total'))
            ->groupBy('business_id')
            ->pluck('total', 'business_id');
    }

    public function show(int $id): JsonResponse
    {
        $business = Business::findOrFail($id);

        $users = User::where('business_id', $id)
            ->where('is_superadmin', false)
            ->with('role')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'role'      => $u->role?->name,
                'is_active' => $u->is_active,
                'joined'    => $u->created_at?->format('Y-m-d'),
            ]);

        $subscriptions = BusinessSubscription::with('plan')
            ->where('business_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'plan'         => $s->plan?->name,
                'status'       => $s->status,
                'starts_at'    => $s->starts_at?->format('Y-m-d'),
                'ends_at'      => $s->ends_at?->format('Y-m-d'),
                'amount_paid'  => (float) $s->amount_paid,
                'payment_method' => $s->payment_method,
                'created_at'   => $s->created_at?->format('Y-m-d'),
            ]);

        return response()->json([
            'business'      => [
                'id'       => $business->id,
                'name'     => $business->business_name,
                'email'    => $business->business_email,
                'phone'    => $business->business_phone,
                'website'  => $business->business_website,
                'industry' => $business->business_industry,
                'location' => $business->business_location,
                'code'     => $business->business_code,
                'logo'     => $business->business_logo,
                'created_at' => $business->created_at?->format('Y-m-d'),
            ],
            'users'         => $users,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function suspend(int $id): JsonResponse
    {
        $business = Business::findOrFail($id);

        // Cancel all active subscriptions
        BusinessSubscription::where('business_id', $id)
            ->whereIn('status', ['trial', 'active'])
            ->update(['status' => 'cancelled']);

        // Deactivate all users of this business
        User::where('business_id', $id)->update(['is_active' => false]);

        return response()->json(['message' => "Business '{$business->business_name}' suspended."]);
    }

    public function reinstate(int $id): JsonResponse
    {
        $business = Business::findOrFail($id);

        // Re-activate all users
        User::where('business_id', $id)->update(['is_active' => true]);

        return response()->json(['message' => "Business '{$business->business_name}' reinstated."]);
    }
}
