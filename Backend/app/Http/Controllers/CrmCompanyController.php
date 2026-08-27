<?php

namespace App\Http\Controllers;

use App\Models\CrmCompany;
use App\Services\CrmCompanyService;
use Illuminate\Http\{JsonResponse, Request};

class CrmCompanyController extends Controller
{
    public function __construct(protected CrmCompanyService $service) {}

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'industry' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CrmCompany::class);
        return response()->json(['data' => $this->service->list($request->query('search'))]);
    }

    public function show(int $id): JsonResponse
    {
        $company = $this->service->find($id);
        $this->authorize('view', $company);
        return response()->json(['data' => $company]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CrmCompany::class);
        $data = $request->validate($this->rules());
        $company = $this->service->create($data);
        return response()->json(['message' => 'Company created.', 'data' => $company], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $company = $this->service->find($id);
        $this->authorize('update', $company);
        $data = $request->validate($this->rules());
        $company = $this->service->update($id, $data);
        return response()->json(['message' => 'Company updated.', 'data' => $company]);
    }

    public function destroy(int $id): JsonResponse
    {
        $company = $this->service->find($id);
        $this->authorize('delete', $company);
        $this->service->delete($id);
        return response()->json(['message' => 'Company deleted.']);
    }
}
