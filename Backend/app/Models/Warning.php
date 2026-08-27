<?php

namespace App\Models;

use App\Traits\LogsHrmChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warning extends Model
{
    use LogsHrmChanges, SoftDeletes;

    protected $fillable = [
        'business_id', 'employee_id', 'warning_type_id', 'warning_date', 'severity',
        'reason', 'description', 'issued_by', 'employee_response', 'supporting_documents',
        'follow_up_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'warning_date' => 'date',
        'follow_up_date' => 'date',
        'supporting_documents' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function warningType(): BelongsTo
    {
        return $this->belongsTo(WarningType::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
