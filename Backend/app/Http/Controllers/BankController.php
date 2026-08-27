<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Services\BankService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    public function __construct(protected BankService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Bank::class);
        return response()->json(['data' => $this->service->list(Auth::user()->business_id, (bool) request('active_only'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Bank::class);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $bank = $this->service->create($data);
        return response()->json(['message' => 'Bank added.', 'data' => $bank], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $bank = $this->service->update($id, $data);
        return response()->json(['message' => 'Bank updated.', 'data' => $bank]);
    }

    public function destroy(int $id): JsonResponse
    {
        $bank = $this->service->find($id);
        $this->authorize('delete', $bank);
        $this->service->delete($id);
        return response()->json(['message' => 'Bank removed.']);
    }
}
