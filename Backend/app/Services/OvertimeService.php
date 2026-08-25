<?php

namespace App\Services;

use App\Models\Overtime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OvertimeService
{
    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Overtime::where('business_id', Auth::user()->business_id)
            ->with(['user:id,name,email', 'approver:id,name'])
            ->orderByDesc('date');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Overtime
    {
        return Overtime::create([
            'business_id'    => Auth::user()->business_id,
            'user_id'        => $data['user_id'],
            'date'           => $data['date'],
            'hours'          => $data['hours'],
            'rate_per_hour'  => $data['rate_per_hour'] ?? 0,
            'reason'         => $data['reason'] ?? null,
            'status'         => 'pending',
        ]);
    }

    public function updateStatus(int $id, string $status): Overtime
    {
        $ot = $this->find($id);
        $ot->update([
            'status'      => $status,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return $ot->load(['user:id,name,email', 'approver:id,name']);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): Overtime
    {
        return Overtime::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
