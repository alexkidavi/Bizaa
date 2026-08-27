<?php

namespace App\Models;

use App\Traits\LogsHrmChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resignation extends Model
{
    use LogsHrmChanges, SoftDeletes;

    protected $fillable = [
        'business_id', 'employee_id', 'resignation_date', 'notice_date', 'notice_period_days',
        'expected_last_working_day', 'actual_last_working_day', 'reason', 'employee_comments',
        'hr_comments', 'manager_comments', 'status', 'supporting_documents',
        'exit_interview_notes', 'exit_interview_conducted_by', 'exit_interview_date', 'created_by',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'notice_date' => 'date',
        'expected_last_working_day' => 'date',
        'actual_last_working_day' => 'date',
        'exit_interview_date' => 'date',
        'supporting_documents' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function exitInterviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exit_interview_conducted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
