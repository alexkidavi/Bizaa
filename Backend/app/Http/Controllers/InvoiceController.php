<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoiceService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Crypt;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->list($request->integer('per_page', 20)));
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->service->create($request->validated());
        $data = $invoice->toArray();
        $data['id'] = Crypt::encryptString($invoice->id);
        return response()->json(['message' => 'Invoice created.', 'data' => $data], 201);
    }

    public function show($id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $invoice = $this->service->find($id);
        $data = $invoice->toArray();
        $data['id'] = Crypt::encryptString($invoice->id);
        return response()->json(['data' => $data]);
    }

    public function update(StoreInvoiceRequest $request, $id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $invoice = $this->service->update($id, $request->validated());
        $data = $invoice->toArray();
        $data['id'] = Crypt::encryptString($invoice->id);
        return response()->json(['message' => 'Invoice updated.', 'data' => $data]);
    }

    public function destroy($id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $this->service->delete($id);
        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function payments($id): JsonResponse
    {
        $id = (int) Crypt::decryptString($id);
        $invoice = $this->service->find($id);
        return response()->json(['data' => $invoice->payments()->with('user')->latest()->get()]);
    }

    public function recordPayment(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'payment_method'    => ['required', 'string'],
            'payment_reference' => ['nullable', 'string'],
            'payment_date'      => ['required', 'date'],
            'notes'             => ['nullable', 'string'],
        ]);
        $id = (int) Crypt::decryptString($id);
        $invoice = $this->service->recordPayment($id, $request->all());
        $data = $invoice->toArray();
        $data['id'] = Crypt::encryptString($invoice->id);
        return response()->json(['message' => 'Payment recorded.', 'data' => $data]);
    }
}
