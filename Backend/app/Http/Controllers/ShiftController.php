<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\{JsonResponse, Request};

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Shift::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Shift::class);
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'description' => 'nullable|string|max:255',
        ]);

        $shift = $this->service->create($data);
        return response()->json(['message' => 'Shift created.', 'data' => $shift], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $shift = $this->service->find($id);
        $this->authorize('update', $shift);
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'description' => 'nullable|string|max:255',
        ]);

        $shift = $this->service->update($id, $data);
        return response()->json(['message' => 'Shift updated.', 'data' => $shift]);
    }

    public function destroy(int $id): JsonResponse
    {
        $shift = $this->service->find($id);
        $this->authorize('delete', $shift);
        $this->service->delete($id);
        return response()->json(['message' => 'Shift deleted.']);
    }
}
