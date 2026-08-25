<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Services\OvertimeService;
use Illuminate\Http\{JsonResponse, Request};

class OvertimeController extends Controller
{
    public function __construct(protected OvertimeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Overtime::class);
        $records = $this->service->list(
            $request->integer('per_page', 50),
            $request->only(['user_id', 'status'])
        );
        return response()->json($records);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Overtime::class);
        $data = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'date'          => 'required|date',
            'hours'         => 'required|numeric|min:0.5',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'reason'        => 'nullable|string|max:255',
        ]);

        $ot = $this->service->create($data);
        return response()->json(['message' => 'Overtime logged.', 'data' => $ot->load('user:id,name,email')], 201);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $ot = $this->service->find($id);
        $this->authorize('update', $ot);
        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $ot = $this->service->updateStatus($id, $data['status']);
        return response()->json(['message' => "Overtime {$data['status']}.", 'data' => $ot]);
    }

    public function destroy(int $id): JsonResponse
    {
        $ot = $this->service->find($id);
        $this->authorize('delete', $ot);
        $this->service->delete($id);
        return response()->json(['message' => 'Overtime record deleted.']);
    }
}
