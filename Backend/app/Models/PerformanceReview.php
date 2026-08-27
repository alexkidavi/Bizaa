<?php

namespace App\Models;

use App\Traits\LogsHrmChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReview extends Model
{
    use LogsHrmChanges, SoftDeletes;

    protected $fillable = [
        'business_id', 'employee_id', 'reviewer_id', 'performance_cycle_id', 'review_date',
        'goals', 'kpis', 'competencies', 'self_assessment', 'manager_assessment',
        'overall_score', 'performance_rating_id', 'strengths', 'areas_for_improvement',
        'development_plan', 'comments', 'status', 'created_by',
    ];

    protected $casts = [
        'review_date' => 'date',
        'overall_score' => 'decimal:1',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function performanceCycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class);
    }

    public function performanceRating(): BelongsTo
    {
        return $this->belongsTo(PerformanceRating::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
