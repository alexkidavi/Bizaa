<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\{JsonResponse, Request};

class LeaveTypeController extends Controller
{
    public function __construct(protected LeaveTypeService $service) {}

    protected function rules(): array
    {
        return [
            'name'                    => 'required|string|max:100',
            'days_allowed'            => 'required|integer|min:0',
            'description'             => 'nullable|string|max:255',
            'accrual_rule'            => 'nullable|in:none,monthly,annual',
            'accrual_rate'            => 'nullable|numeric|min:0',
            'carry_forward_allowed'   => 'nullable|boolean',
            'max_carry_forward_days'  => 'nullable|integer|min:0',
            'gender_eligibility'      => 'nullable|in:male,female',
            'requires_documentation'  => 'nullable|boolean',
            'requires_approval'       => 'nullable|boolean',
            'is_paid'                 => 'nullable|boolean',
            'is_active'               => 'nullable|boolean',
        ];
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', LeaveType::class);
        return response()->json(['data' => $this->service->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LeaveType::class);
        $data = $request->validate($this->rules());

        $lt = $this->service->create($data);
        return response()->json(['message' => 'Leave type created.', 'data' => $lt], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $lt = $this->service->find($id);
        $this->authorize('update', $lt);
        $data = $request->validate($this->rules());

        $lt = $this->service->update($id, $data);
        return response()->json(['message' => 'Leave type updated.', 'data' => $lt]);
    }

    public function destroy(int $id): JsonResponse
    {
        $lt = $this->service->find($id);
        $this->authorize('delete', $lt);
        $this->service->delete($id);
        return response()->json(['message' => 'Leave type deleted.']);
    }
}
