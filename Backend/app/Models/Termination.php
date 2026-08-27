<?php

namespace App\Models;

use App\Traits\LogsHrmChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Termination extends Model
{
    use LogsHrmChanges, SoftDeletes;

    protected $fillable = [
        'business_id', 'employee_id', 'termination_type_id', 'termination_date', 'effective_date',
        'reason', 'detailed_explanation', 'notice_given', 'notice_period_days', 'hr_comments',
        'manager_comments', 'supporting_documents', 'final_settlement_amount', 'final_settlement_notes',
        'status', 'approved_by', 'approved_at', 'created_by',
    ];

    protected $casts = [
        'termination_date' => 'date',
        'effective_date' => 'date',
        'notice_given' => 'boolean',
        'supporting_documents' => 'array',
        'final_settlement_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function terminationType(): BelongsTo
    {
        return $this->belongsTo(TerminationType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
