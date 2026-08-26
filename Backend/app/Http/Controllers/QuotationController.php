<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Crypt;

class QuotationController extends Controller
{
    public function __construct(protected QuotationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Quotation::class);

        return response()->json($this->service->list($request->integer('per_page', 20)));
    }

    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $this->authorize('create', Quotation::class);

        $quotation = $this->service->create($request->validated());
        $data = $quotation->toArray();
        $data['id'] = Crypt::encryptString($quotation->id);
        return response()->json(['message' => 'Quotation created.', 'data' => $data], 201);
    }

    public function show($id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $quotation = $this->service->find($id);
        $this->authorize('view', $quotation);
        $data = $quotation->toArray();
        $data['id'] = Crypt::encryptString($quotation->id);
        return response()->json(['data' => $data]);
    }

    public function update(StoreQuotationRequest $request, $id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $quotation = $this->service->update($id, $request->validated());
        $data = $quotation->toArray();
        $data['id'] = Crypt::encryptString($quotation->id);
        return response()->json(['message' => 'Quotation updated.', 'data' => $data]);
    }

    public function destroy($id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $quotation = $this->service->find($id);
        $this->authorize('delete', $quotation);
        $this->service->delete($id);
        return response()->json(['message' => 'Quotation deleted.']);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => ['required', 'in:draft,sent,accepted,rejected,expired']]);
        $id = (int) Crypt::decryptString($id);
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $quotation = $this->service->updateStatus($id, $request->status);
        $data = $quotation->toArray();
        $data['id'] = Crypt::encryptString($quotation->id);
        return response()->json(['message' => 'Status updated.', 'data' => $data]);
    }

    public function convertToInvoice($id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);
        $invoice = $this->service->convertToInvoice($id);
        $data = $invoice->toArray();
        $data['id'] = Crypt::encryptString($invoice->id);
        return response()->json(['message' => 'Quotation converted to invoice.', 'data' => $data], 201);
    }
}
