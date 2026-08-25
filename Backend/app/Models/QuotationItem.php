<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = ['quotation_id','product_id','description','quantity','unit_price','discount','tax','line_total'];
    protected $casts    = ['quantity'=>'float','unit_price'=>'float','discount'=>'float','tax'=>'float','line_total'=>'float'];

    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function product(): BelongsTo   { return $this->belongsTo(Product::class)->withTrashed(); }
}