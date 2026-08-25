<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\{JsonResponse, Request};

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);
        $records = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['user_id', 'date', 'from', 'to', 'status'])
        );
        return response()->json($records);
    }


    
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);
        $data = $request->validate([
            'user_id'   => 'required|integer|exists:users,id',
            'date'      => 'required|date',
            'check_in'  => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'shift_id'  => 'nullable|integer|exists:shifts,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'status'    => 'nullable|in:present,absent,late,half_day,leave',
            'notes'     => 'nullable|string|max:255',
        ]);

        $record = $this->service->upsert($data);
        return response()->json(['message' => 'Attendance recorded.', 'data' => $record->load(['user:id,name', 'shift:id,name'])], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Attendance::class);
        $this->service->delete($id);
        return response()->json(['message' => 'Attendance record deleted.']);
    }
}
