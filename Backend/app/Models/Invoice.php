<?php
// =====================================================================
// app/Models/Invoice.php
// =====================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id','user_id','customer_id','branch_id','quotation_id',
        'invoice_number','issue_date','due_date','notes','currency',
        'subtotal','total_discount','total_tax','grand_total',
        'paid_amount','balance','payment_type','payment_status',
        'payment_method','payment_reference',
    ];

    protected $casts = [
        'issue_date'     => 'date',
        'due_date'       => 'date',
        'subtotal'       => 'float',
        'total_discount' => 'float',
        'total_tax'      => 'float',
        'grand_total'    => 'float',
        'paid_amount'    => 'float',
        'balance'        => 'float',
    ];

    public function business(): BelongsTo  { return $this->belongsTo(Business::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo  { return $this->belongsTo(Customer::class)->withTrashed(); }
    public function branch(): BelongsTo    { return $this->belongsTo(Branch::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function items(): HasMany       { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany    { return $this->hasMany(InvoicePayment::class); }

    public function scopeForBusiness($q, int $id) { return $q->where('business_id', $id); }
    public function getIsOverdueAttribute(): bool  { return $this->payment_status !== 'paid' && $this->due_date && $this->due_date->isPast(); }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $year  = now()->format('Y');
                $month = now()->format('m');
                $count = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
                $invoice->invoice_number = sprintf('INV-%s%s-%04d', $year, $month, $count);
            }
        });
    }
}
