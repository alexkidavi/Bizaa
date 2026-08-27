<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmDeal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'contact_id', 'company_id', 'pipeline_stage_id', 'title', 'value',
        'probability', 'expected_close_date', 'assigned_to', 'status', 'lost_reason',
        'quotation_id', 'sale_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'decimal:2',
        'expected_close_date' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CrmCompany::class, 'company_id');
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(CrmPipelineStage::class, 'pipeline_stage_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'deal_id');
    }
}
