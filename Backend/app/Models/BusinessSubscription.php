<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class BusinessSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'status',
        'amount_paid',
        'payment_method',
        'payment_reference',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active'])
            && $this->ends_at->isFuture();
    }

    public function daysRemaining(): int
    {
        if (!$this->isActive()) return 0;
        return (int) max(0, now()->diffInDays($this->ends_at, false));
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActiveForBusiness($query, int $businessId)
    {
        return $query
            ->where('business_id', $businessId)
            ->whereIn('status', ['trial', 'active'])
            ->where('ends_at', '>', now())
            ->orderByDesc('ends_at')
            ->limit(1);
    }

    // ── Static factory ────────────────────────────────────────────────────

    public static function createTrial(int $businessId): self
    {
        $plan = SubscriptionPlan::where('slug', 'trial')->first();
        if (!$plan) {
            return self::create([
                'business_id' => $businessId,
                'plan_id'     => 1,
                'starts_at'   => now(),
                'ends_at'     => now()->addDays(14),
                'status'      => 'trial',
                'amount_paid' => 0,
            ]);
        }
        return self::create([
            'business_id' => $businessId,
            'plan_id'     => $plan->id,
            'starts_at'   => now(),
            'ends_at'     => now()->addDays($plan->duration_days),
            'status'      => 'trial',
            'amount_paid' => 0,
        ]);
    }
}
