<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = ['invoice_id','user_id','amount','payment_method','payment_reference','notes','payment_date'];
    protected $casts    = ['payment_date'=>'date','amount'=>'float'];
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
}