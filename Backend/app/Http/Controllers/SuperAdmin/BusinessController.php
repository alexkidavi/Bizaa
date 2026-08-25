<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubscription;
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

        // User counts per business
        $userCounts = User::whereIn('business_id', $businessIds)
            ->where('is_superadmin', false)
            ->select('business_id', DB::raw('count(*) as total'))
            ->groupBy('business_id')
            ->pluck('total', 'business_id');

        $data = $businesses->through(fn($b) => [
            'id'           => $b->id,
            'name'         => $b->business_name,
            'email'        => $b->business_email,
            'phone'        => $b->business_phone,
            'industry'     => $b->business_industry,
            'location'     => $b->business_location,
            'code'         => $b->business_code,
            'created_at'   => $b->created_at?->format('Y-m-d'),
            'user_count'   => $userCounts[$b->id] ?? 0,
            'subscription' => isset($activeSubs[$b->id]) ? [
                'status'   => $activeSubs[$b->id]->status,
                'ends_at'  => $activeSubs[$b->id]->ends_at?->format('Y-m-d'),
                'days_left'=> (int) now()->diffInDays($activeSubs[$b->id]->ends_at, false),
            ] : null,
        ]);

        return response()->json($data);
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
