<?php

namespace App\Http\Controllers;

use App\Models\PayrollTaxBand;
use App\Services\PayrollTaxBandService;
use Illuminate\Http\{JsonResponse, Request};

class PayrollTaxBandController extends Controller
{
    public function __construct(protected PayrollTaxBandService $service) {}

    protected function rules(): array
    {
        return [
            'effective_date' => 'required|date',
            'lower_limit' => 'required|numeric|min:0',
            'upper_limit' => 'nullable|numeric|gt:lower_limit',
            'rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', PayrollTaxBand::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PayrollTaxBand::class);
        $data = $request->validate($this->rules());

        $band = $this->service->create($data);
        return response()->json(['message' => 'Tax band created.', 'data' => $band], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $band = $this->service->find($id);
        $this->authorize('update', $band);
        $data = $request->validate($this->rules());

        $band = $this->service->update($id, $data);
        return response()->json(['message' => 'Tax band updated.', 'data' => $band]);
    }

    public function destroy(int $id): JsonResponse
    {
        $band = $this->service->find($id);
        $this->authorize('delete', $band);
        $this->service->delete($id);
        return response()->json(['message' => 'Tax band deleted.']);
    }
}
