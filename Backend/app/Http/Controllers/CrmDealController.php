<?php

namespace App\Http\Controllers;

use App\Models\CrmDeal;
use App\Services\CrmDealService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Auth;

class CrmDealController extends Controller
{
    public function __construct(protected CrmDealService $service) {}

    protected function rules(bool $requireContact = true): array
    {
        $businessId = Auth::user()->business_id;

        return [
            'contact_id' => ($requireContact ? 'required' : 'nullable') . "|integer|exists:crm_contacts,id,business_id,{$businessId}",
            'company_id' => "nullable|integer|exists:crm_companies,id,business_id,{$businessId}",
            'pipeline_stage_id' => "nullable|integer|exists:crm_pipeline_stages,id,business_id,{$businessId}",
            'title' => 'required|string|max:200',
            'value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CrmDeal::class);
        return response()->json(['data' => $this->service->list(
            $request->only(['status', 'pipeline_stage_id', 'assigned_to', 'contact_id'])
        )]);
    }

    public function show(int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('view', $deal);
        return response()->json(['data' => $deal]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmDeal::class);
        $data = $request->validate($this->rules());
        $deal = $this->service->create($data);
        return response()->json(['message' => 'Deal created.', 'data' => $deal], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('update', $deal);
        $data = $request->validate($this->rules(false));
        $deal = $this->service->update($id, $data);
        return response()->json(['message' => 'Deal updated.', 'data' => $deal]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('delete', $deal);
        $this->service->delete($id);
        return response()->json(['message' => 'Deal deleted.']);
    }

    public function moveStage(Request $request, int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('update', $deal);
        $data = $request->validate(['pipeline_stage_id' => 'required|integer']);
        $deal = $this->service->moveStage($id, $data['pipeline_stage_id']);
        return response()->json(['message' => 'Deal moved.', 'data' => $deal]);
    }

    public function markWon(int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('update', $deal);
        $deal = $this->service->markWon($id);
        return response()->json(['message' => 'Deal marked as won.', 'data' => $deal]);
    }

    public function markLost(Request $request, int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('update', $deal);
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $deal = $this->service->markLost($id, $data['reason']);
        return response()->json(['message' => 'Deal marked as lost.', 'data' => $deal]);
    }

    public function linkQuotation(Request $request, int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('update', $deal);
        $data = $request->validate(['quotation_id' => 'required|integer']);
        $deal = $this->service->linkQuotation($id, $data['quotation_id']);
        return response()->json(['message' => 'Quotation linked.', 'data' => $deal]);
    }

    public function linkSale(Request $request, int $id): JsonResponse
    {
        $deal = $this->service->find($id);
        $this->authorize('update', $deal);
        $data = $request->validate(['sale_id' => 'required|integer']);
        $deal = $this->service->linkSale($id, $data['sale_id']);
        return response()->json(['message' => 'Sale linked.', 'data' => $deal]);
    }
}
