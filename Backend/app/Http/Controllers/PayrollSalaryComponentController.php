<?php

namespace App\Http\Controllers;

use App\Models\PayrollSalaryComponent;
use App\Services\PayrollSalaryComponentService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class PayrollSalaryComponentController extends Controller
{
    public function __construct(protected PayrollSalaryComponentService $service) {}

    protected function rules(int $businessId, ?int $ignoreId = null): array
    {
        return [
            'name' => 'required|string|max:100',
            'code' => [
                'required', 'string', 'max:30',
                Rule::unique('payroll_salary_components', 'code')
                    ->where('business_id', $businessId)
                    ->ignore($ignoreId),
            ],
            'type' => 'required|in:earning,deduction',
            'calculation_type' => 'required|in:fixed,percentage_of_basic',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'is_taxable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PayrollSalaryComponent::class);
        return response()->json(['data' => $this->service->list($request->boolean('active_only'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PayrollSalaryComponent::class);
        $data = $request->validate($this->rules($request->user()->business_id));

        $component = $this->service->create($data);
        return response()->json(['message' => 'Salary component created.', 'data' => $component], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $component = $this->service->find($id);
        $this->authorize('update', $component);
        $data = $request->validate($this->rules($request->user()->business_id, $id));

        $component = $this->service->update($id, $data);
        return response()->json(['message' => 'Salary component updated.', 'data' => $component]);
    }

    public function destroy(int $id): JsonResponse
    {
        $component = $this->service->find($id);
        $this->authorize('delete', $component);
        $this->service->delete($id);
        return response()->json(['message' => 'Salary component deleted.']);
    }
}
