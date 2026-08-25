<?php

namespace App\Services;

use App\Models\Leave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LeaveService
{
    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Leave::where('business_id', Auth::user()->business_id)
            ->with(['user:id,name,email', 'leaveType:id,name', 'approver:id,name'])
            ->orderByDesc('created_at');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Leave
    {
        $days = (int) now()->parse($data['start_date'])->diffInDays(now()->parse($data['end_date'])) + 1;

        return Leave::create([
            'business_id'   => Auth::user()->business_id,
            'user_id'       => $data['user_id'],
            'leave_type_id' => $data['leave_type_id'],
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'days'          => $data['days'] ?? $days,
            'reason'        => $data['reason'] ?? null,
            'status'        => 'pending',
        ]);
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): Leave
    {
        $leave = $this->find($id);
        $leave->update([
            'status'      => $status,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes'       => $notes,
        ]);
        return $leave->load(['user:id,name,email', 'leaveType:id,name', 'approver:id,name']);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): Leave
    {
        return Leave::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
