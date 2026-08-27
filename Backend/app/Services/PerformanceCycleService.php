<?php

namespace App\Services;

use App\Models\PerformanceCycle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PerformanceCycleService
{
    public function list(): Collection
    {
        return PerformanceCycle::where('business_id', Auth::user()->business_id)
            ->orderByDesc('start_date')
            ->get();
    }

    public function find(int $id): PerformanceCycle
    {
        return PerformanceCycle::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function create(array $data): PerformanceCycle
    {
        return PerformanceCycle::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'custom',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'] ?? 'draft',
            'created_by' => Auth::id(),
        ]);
    }

    public function update(int $id, array $data): PerformanceCycle
    {
        $cycle = $this->find($id);
        $cycle->update([
            'name' => $data['name'],
            'type' => $data['type'] ?? 'custom',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'] ?? $cycle->status,
        ]);

        return $cycle;
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }
}
