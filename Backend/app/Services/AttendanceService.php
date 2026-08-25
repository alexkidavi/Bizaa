<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Attendance::where('business_id', Auth::user()->business_id)
            ->with(['user:id,name,email', 'shift:id,name', 'branch:id,name'])
            ->orderByDesc('date');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('date', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('date', '<=', $filters['to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function upsert(array $data): Attendance
    {
        return Attendance::updateOrCreate(
            ['user_id' => $data['user_id'], 'date' => $data['date']],
            [
                'business_id' => Auth::user()->business_id,
                'branch_id'   => $data['branch_id'] ?? null,
                'shift_id'    => $data['shift_id'] ?? null,
                'check_in'    => $data['check_in'] ?? null,
                'check_out'   => $data['check_out'] ?? null,
                'status'      => $data['status'] ?? 'present',
                'notes'       => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]
        );
    }

    public function delete(int $id): void
    {
        Attendance::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail()
            ->delete();
    }
}
