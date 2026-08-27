<?php

namespace App\Http\Controllers;

use App\Models\PayrollStatutoryRate;
use App\Services\PayrollStatutoryRateService;
use Illuminate\Http\{JsonResponse, Request};

class PayrollStatutoryRateController extends Controller
{
    public function __construct(protected PayrollStatutoryRateService $service) {}

    protected function rules(): array
    {
        return [
            'scheme' => 'required|in:nssf,shif,housing_levy',
            'effective_date' => 'required|date',
            'lower_limit' => 'nullable|numeric|min:0',
            'upper_limit' => 'nullable|numeric|gt:lower_limit',
            'employee_rate' => 'nullable|numeric|min:0|max:100',
            'employer_rate' => 'nullable|numeric|min:0|max:100',
            'minimum_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PayrollStatutoryRate::class);
        return response()->json(['data' => $this->service->list($request->query('scheme'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PayrollStatutoryRate::class);
        $data = $request->validate($this->rules());

        $rate = $this->service->create($data);
        return response()->json(['message' => 'Statutory rate created.', 'data' => $rate], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rate = $this->service->find($id);
        $this->authorize('update', $rate);
        $data = $request->validate($this->rules());

        $rate = $this->service->update($id, $data);
        return response()->json(['message' => 'Statutory rate updated.', 'data' => $rate]);
    }

    public function destroy(int $id): JsonResponse
    {
        $rate = $this->service->find($id);
        $this->authorize('delete', $rate);
        $this->service->delete($id);
        return response()->json(['message' => 'Statutory rate deleted.']);
    }
}
