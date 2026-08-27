<?php

namespace App\Services;

use App\Models\PerformanceRating;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PerformanceRatingService
{
    public function list(bool $activeOnly = false): Collection
    {
        $query = PerformanceRating::where('business_id', Auth::user()->business_id)->orderByDesc('score_value');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function find(int $id): PerformanceRating
    {
        return PerformanceRating::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function create(array $data): PerformanceRating
    {
        return PerformanceRating::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'score_value' => $data['score_value'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(int $id, array $data): PerformanceRating
    {
        $rating = $this->find($id);
        $rating->update([
            'name' => $data['name'],
            'score_value' => $data['score_value'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $rating;
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function seedDefaults(int $businessId): void
    {
        $defaults = [
            ['name' => 'Outstanding', 'score_value' => 5],
            ['name' => 'Exceeds Expectations', 'score_value' => 4],
            ['name' => 'Meets Expectations', 'score_value' => 3],
            ['name' => 'Needs Improvement', 'score_value' => 2],
            ['name' => 'Unsatisfactory', 'score_value' => 1],
        ];

        foreach ($defaults as $rating) {
            PerformanceRating::firstOrCreate(
                ['business_id' => $businessId, 'name' => $rating['name']],
                ['score_value' => $rating['score_value'], 'is_active' => true]
            );
        }
    }
}
