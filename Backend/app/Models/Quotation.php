<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id','user_id','customer_id','branch_id','invoice_id',
        'quotation_number','status','issue_date','expiry_date','currency','notes',
        'subtotal','total_discount','total_tax','grand_total',
    ];

    protected $casts = [
        'issue_date'     => 'date',
        'expiry_date'    => 'date',
        'subtotal'       => 'float',
        'total_discount' => 'float',
        'total_tax'      => 'float',
        'grand_total'    => 'float',
    ];

    public function business(): BelongsTo  { return $this->belongsTo(Business::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo  { return $this->belongsTo(Customer::class); }
    public function branch(): BelongsTo    { return $this->belongsTo(Branch::class); }
    public function invoice(): BelongsTo   { return $this->belongsTo(Invoice::class); }
    public function items(): HasMany       { return $this->hasMany(QuotationItem::class); }

    public function scopeForBusiness($q, int $id) { return $q->where('business_id', $id); }
    public function getIsExpiredAttribute(): bool  { return !in_array($this->status, ['accepted','converted']) && $this->expiry_date && $this->expiry_date->isPast(); }

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            if (empty($quotation->quotation_number)) {
                $year  = now()->format('Y');
                $month = now()->format('m');
                $count = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $quotation->quotation_number = sprintf('QT-%s%s-%04d', $year, $month, $count);
            }
        });
    }
}