<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id','product_id','description','quantity','unit_price','discount','tax','line_total'];
    protected $casts    = ['quantity'=>'float','unit_price'=>'float','discount'=>'float','tax'=>'float','line_total'=>'float'];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class)->withTrashed(); }
}