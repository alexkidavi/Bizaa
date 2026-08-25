<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusinessSubscription::with(['business', 'plan'])
            ->orderByDesc('created_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('business', fn($q) =>
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%")
            );
        }

        $subs = $query->paginate(25);

        return response()->json($subs->through(fn($s) => [
            'id'             => $s->id,
            'business_id'    => $s->business_id,
            'business_name'  => $s->business?->business_name,
            'plan'           => $s->plan?->name,
            'status'         => $s->status,
            'starts_at'      => $s->starts_at?->format('Y-m-d'),
            'ends_at'        => $s->ends_at?->format('Y-m-d'),
            'days_left'      => $s->isActive() ? (int) now()->diffInDays($s->ends_at, false) : 0,
            'amount_paid'    => (float) $s->amount_paid,
            'payment_method' => $s->payment_method,
            'created_at'     => $s->created_at?->format('Y-m-d'),
        ]));
    }

    public function plans(): JsonResponse
    {
        return response()->json(SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get());
    }

    public function grant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'plan_id'     => 'required|exists:subscription_plans,id',
            'days'        => 'required|integer|min:1|max:3650',
            'amount_paid' => 'sometimes|numeric|min:0',
            'notes'       => 'sometimes|nullable|string|max:500',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Cancel any existing active subscription
        BusinessSubscription::where('business_id', $validated['business_id'])
            ->whereIn('status', ['trial', 'active'])
            ->update(['status' => 'cancelled']);

        $sub = BusinessSubscription::create([
            'business_id'      => $validated['business_id'],
            'plan_id'          => $plan->id,
            'starts_at'        => now(),
            'ends_at'          => now()->addDays($validated['days']),
            'status'           => 'active',
            'amount_paid'      => $validated['amount_paid'] ?? 0,
            'payment_method'   => 'manual',
            'payment_reference'=> 'SUPERADMIN-GRANT',
            'recorded_by'      => $request->user()->id,
            'notes'            => $validated['notes'] ?? "Granted by super admin",
        ]);

        return response()->json([
            'message' => 'Subscription granted successfully.',
            'subscription' => $sub,
        ], 201);
    }

    public function revoke(int $id): JsonResponse
    {
        $sub = BusinessSubscription::findOrFail($id);
        $sub->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Subscription cancelled.']);
    }

    public function extend(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:3650',
        ]);

        $sub = BusinessSubscription::findOrFail($id);

        $newEnd = ($sub->ends_at && $sub->ends_at->isFuture())
            ? $sub->ends_at->addDays($validated['days'])
            : now()->addDays($validated['days']);

        $sub->update([
            'ends_at' => $newEnd,
            'status'  => 'active',
        ]);

        return response()->json(['message' => "Extended by {$validated['days']} days.", 'ends_at' => $newEnd->format('Y-m-d')]);
    }
}
