<?php

namespace App\Http\Controllers;

use App\Models\CrmCampaign;
use App\Services\CrmCampaignService;
use Illuminate\Http\{JsonResponse, Request};

class CrmCampaignController extends Controller
{
    public function __construct(protected CrmCampaignService $service) {}

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'channel' => 'required|in:sms,whatsapp,email',
            'subject' => 'nullable|required_if:channel,email|string|max:150',
            'message' => 'required|string|max:5000',
            'filters' => 'nullable|array',
            'filters.lifecycle_stage' => 'nullable|in:lead,qualified,customer,lost',
            'filters.company_id' => 'nullable|integer',
            'filters.source_id' => 'nullable|integer',
            'filters.tag_id' => 'nullable|integer',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CrmCampaign::class);
        return response()->json(['data' => $this->service->list($request->only(['channel', 'status']))]);
    }

    public function show(int $id): JsonResponse
    {
        $campaign = $this->service->find($id);
        $this->authorize('view', $campaign);
        return response()->json(['data' => $campaign]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmCampaign::class);
        $data = $request->validate($this->rules());
        $campaign = $this->service->create($data);
        return response()->json(['message' => 'Campaign created.', 'data' => $campaign], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = $this->service->find($id);
        $this->authorize('update', $campaign);
        $data = $request->validate($this->rules());
        $campaign = $this->service->update($id, $data);
        return response()->json(['message' => 'Campaign updated.', 'data' => $campaign]);
    }

    public function destroy(int $id): JsonResponse
    {
        $campaign = $this->service->find($id);
        $this->authorize('delete', $campaign);
        $this->service->delete($id);
        return response()->json(['message' => 'Campaign deleted.']);
    }

    public function previewRecipients(Request $request): JsonResponse
    {
        $this->authorize('create', CrmCampaign::class);
        $data = $request->validate([
            'channel' => 'required|in:sms,whatsapp,email',
            'filters' => 'nullable|array',
        ]);
        return response()->json($this->service->previewRecipients($data['channel'], $data['filters'] ?? []));
    }

    public function send(int $id): JsonResponse
    {
        $campaign = $this->service->find($id);
        $this->authorize('update', $campaign);
        $campaign = $this->service->send($id);
        return response()->json(['message' => "Campaign {$campaign->status}.", 'data' => $campaign]);
    }
}
