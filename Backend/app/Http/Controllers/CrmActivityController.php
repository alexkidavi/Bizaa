<?php

namespace App\Http\Controllers;

use App\Models\CrmActivity;
use App\Services\CrmActivityService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Auth;

class CrmActivityController extends Controller
{
    public function __construct(protected CrmActivityService $service) {}

    protected function rules(bool $requireContact = true): array
    {
        $businessId = Auth::user()->business_id;

        return [
            'contact_id' => ($requireContact ? 'required_without:deal_id' : 'nullable') . "|integer|exists:crm_contacts,id,business_id,{$businessId}",
            'deal_id' => "nullable|integer|exists:crm_deals,id,business_id,{$businessId}",
            'type' => 'required|in:call,meeting,email,whatsapp,sms,note,task',
            'subject' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer|exists:users,id',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CrmActivity::class);
        return response()->json(['data' => $this->service->list(
            $request->only(['contact_id', 'deal_id', 'assigned_to', 'type', 'status'])
        )]);
    }

    public function show(int $id): JsonResponse
    {
        $activity = $this->service->find($id);
        $this->authorize('view', $activity);
        return response()->json(['data' => $activity]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmActivity::class);
        $data = $request->validate($this->rules());
        $activity = $this->service->create($data);
        return response()->json(['message' => 'Activity logged.', 'data' => $activity], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $activity = $this->service->find($id);
        $this->authorize('update', $activity);
        $data = $request->validate($this->rules(false));
        $activity = $this->service->update($id, $data);
        return response()->json(['message' => 'Activity updated.', 'data' => $activity]);
    }

    public function destroy(int $id): JsonResponse
    {
        $activity = $this->service->find($id);
        $this->authorize('delete', $activity);
        $this->service->delete($id);
        return response()->json(['message' => 'Activity deleted.']);
    }

    public function complete(int $id): JsonResponse
    {
        $activity = $this->service->find($id);
        $this->authorize('update', $activity);
        $activity = $this->service->complete($id);
        return response()->json(['message' => 'Marked complete.', 'data' => $activity]);
    }

    public function reopen(int $id): JsonResponse
    {
        $activity = $this->service->find($id);
        $this->authorize('update', $activity);
        $activity = $this->service->reopen($id);
        return response()->json(['message' => 'Reopened.', 'data' => $activity]);
    }
}
