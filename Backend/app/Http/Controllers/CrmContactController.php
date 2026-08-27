<?php

namespace App\Http\Controllers;

use App\Models\CrmContact;
use App\Services\CrmContactService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Auth;

class CrmContactController extends Controller
{
    public function __construct(protected CrmContactService $service) {}

    protected function rules(): array
    {
        $businessId = Auth::user()->business_id;

        return [
            'company_id' => "nullable|integer|exists:crm_companies,id,business_id,{$businessId}",
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:20',
            'source_id' => "nullable|integer|exists:crm_sources,id,business_id,{$businessId}",
            'lifecycle_stage' => 'nullable|in:lead,qualified,customer,lost',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'notes' => 'nullable|string|max:2000',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => "integer|exists:crm_tags,id,business_id,{$businessId}",
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CrmContact::class);
        return response()->json($this->service->list(
            $request->integer('per_page', 50),
            $request->only(['search', 'lifecycle_stage', 'company_id', 'assigned_to', 'tag_id'])
        ));
    }

    public function show(int $id): JsonResponse
    {
        $contact = $this->service->find($id);
        $this->authorize('view', $contact);
        return response()->json(['data' => $this->service->findWithDeals($id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmContact::class);
        $data = $request->validate($this->rules());
        $contact = $this->service->create($data);
        return response()->json(['message' => 'Contact created.', 'data' => $contact], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $contact = $this->service->find($id);
        $this->authorize('update', $contact);
        $data = $request->validate($this->rules());
        $contact = $this->service->update($id, $data);
        return response()->json(['message' => 'Contact updated.', 'data' => $contact]);
    }

    public function destroy(int $id): JsonResponse
    {
        $contact = $this->service->find($id);
        $this->authorize('delete', $contact);
        $this->service->delete($id);
        return response()->json(['message' => 'Contact deleted.']);
    }

    public function convertToCustomer(int $id): JsonResponse
    {
        $contact = $this->service->find($id);
        $this->authorize('update', $contact);
        $contact = $this->service->convertToCustomer($id);
        return response()->json(['message' => 'Contact converted to customer.', 'data' => $contact]);
    }
}
