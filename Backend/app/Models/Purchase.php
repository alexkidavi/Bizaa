<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'supplier_id',
        'reference',
        'purchase_date',
        'notes',
        'subtotal',
        'total_discount',
        'total_tax',
        'grand_total',
        'paid_amount',
        'balance',
        'payment_type',
        'payment_status',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'subtotal'       => 'float',
        'total_discount' => 'float',
        'total_tax'      => 'float',
        'grand_total'    => 'float',
        'paid_amount'    => 'float',
        'balance'        => 'float',
    ];

    // ── Relationships ──────────────────────────────

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        // withTrashed() ensures purchases still resolve their supplier
        // even after the supplier has been soft-deleted.
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'purchase_branch')
                    ->withPivot('subtotal')
                    ->withTimestamps();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    // ── Auto-generate reference ────────────────────

    protected static function booted(): void
    {
        static::creating(function (Purchase $purchase) {
            if (empty($purchase->reference)) {
                $year      = now()->format('Y');
                $month     = now()->format('m');
                $count     = static::whereYear('created_at', $year)
                                   ->whereMonth('created_at', $month)
                                   ->count() + 1;
                $purchase->reference = sprintf('PO-%s%s-%04d', $year, $month, $count);
            }
        });
    }
}
