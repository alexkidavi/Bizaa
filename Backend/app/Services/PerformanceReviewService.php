<?php

namespace App\Services;

use App\Models\PerformanceReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PerformanceReviewService
{
    protected const RELATIONS = [
        'employee:id,first_name,middle_name,last_name,employee_number',
        'reviewer:id,name',
        'performanceCycle:id,name,type',
        'performanceRating:id,name,score_value',
        'creator:id,name',
    ];

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = PerformanceReview::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('review_date');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['performance_cycle_id'])) {
            $query->where('performance_cycle_id', $filters['performance_cycle_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): PerformanceReview
    {
        return PerformanceReview::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    public function create(array $data): PerformanceReview
    {
        $auth = Auth::user();

        $payload = $this->basePayload($data);
        $payload['business_id'] = $auth->business_id;
        $payload['employee_id'] = $data['employee_id'];
        $payload['performance_cycle_id'] = $data['performance_cycle_id'];
        $payload['reviewer_id'] = $data['reviewer_id'] ?? $auth->id;
        $payload['status'] = $data['status'] ?? 'draft';
        $payload['created_by'] = $auth->id;

        $review = PerformanceReview::create($payload);

        return $review->fresh(self::RELATIONS);
    }

    public function update(int $id, array $data): PerformanceReview
    {
        $review = $this->find($id);
        $payload = $this->basePayload($data);

        if (array_key_exists('reviewer_id', $data) && $data['reviewer_id']) {
            $payload['reviewer_id'] = $data['reviewer_id'];
        }
        if (array_key_exists('status', $data) && $data['status']) {
            $payload['status'] = $data['status'];
        }

        $review->update($payload);

        return $review->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    protected function basePayload(array $data): array
    {
        return [
            'review_date' => $data['review_date'] ?? null,
            'goals' => $data['goals'] ?? null,
            'kpis' => $data['kpis'] ?? null,
            'competencies' => $data['competencies'] ?? null,
            'self_assessment' => $data['self_assessment'] ?? null,
            'manager_assessment' => $data['manager_assessment'] ?? null,
            'overall_score' => $data['overall_score'] ?? null,
            'performance_rating_id' => $data['performance_rating_id'] ?? null,
            'strengths' => $data['strengths'] ?? null,
            'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
            'development_plan' => $data['development_plan'] ?? null,
            'comments' => $data['comments'] ?? null,
        ];
    }
}
