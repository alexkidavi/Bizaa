<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PurchaseController extends Controller
{
    use HandlesDeleteErrors;

    public function __construct(protected PurchaseService $service) {}

    /**
     * GET /api/purchases
     * List all purchases for the authenticated business (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Purchase::class);

        $purchases = $this->service->list(
            $request->integer('per_page', 20)
        );
        $summary = $this->service->summary();

        return response()->json(array_merge($purchases->toArray(), [
            'summary' => $summary,
        ]));
    }

    /**
     * POST /api/purchases
     * Create a new purchase with items, branch assignments, and optional payment.
     */
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $this->authorize('create', Purchase::class);

        $purchase = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Purchase created successfully.',
            'data'    => $purchase,
        ], 201);
    }

    /**
     * GET /api/purchases/{id}
     * Show a single purchase with all relations.
     */
    public function show($id): JsonResponse
    {

        $purchase = $this->service->find($id);
        $this->authorize('view', $purchase);

        return response()->json(['data' => $purchase]);
    }

    /**
     * PUT /api/purchases/{id}
     * Update an existing purchase.
     */
    public function update(StorePurchaseRequest $request, int $id): JsonResponse
    {
        $existing = $this->service->find((string) $id);
        $this->authorize('update', $existing);

        $purchase = $this->service->update($id, $request->validated());

        return response()->json([
            'message' => 'Purchase updated successfully.',
            'data'    => $purchase,
        ]);
    }

    /**
     * DELETE /api/purchases/{id}
     * Soft-delete a purchase.
     */
    public function destroy($id): JsonResponse
    {
        $purchase = $this->service->find($id);
        $this->authorize('delete', $purchase);

        return $this->safeDelete(fn () => $this->service->delete($id), 'Purchase deleted successfully');
    }

    /**
     * GET /api/purchases/{id}/payments
     * List all payment records for a purchase.
     */
    public function payments(string $id): JsonResponse
    {
        $purchase = $this->service->find($id);
        $this->authorize('view', $purchase);

        return response()->json([
            'data' => $purchase->payments()->with('user')->latest()->get(),
        ]);
    }

    /**
     * GET /api/purchases/{id}/items
     * List all items for a purchase, grouped by branch.
     */
    public function items($id): JsonResponse
    {
        $purchase = $this->service->find($id);
        $this->authorize('view', $purchase);
        $grouped = $purchase->items()
            ->with(['product', 'branch'])
            ->get()
            ->groupBy('branch_id')
            ->map(fn ($items) => [
                'branch'   => $items->first()->branch,
                'items'    => $items->values(),
                'subtotal' => $items->sum('line_total'),
            ])
            ->values();

        return response()->json(['data' => $grouped]);
    }

    /**
     * POST /api/purchases/{id}/payments
     * Record an additional payment against an existing purchase.
     */
    public function recordPayment(Request $request, string $id): JsonResponse
    {
         $id=Crypt::decryptString($id);

        $existing = $this->service->find(Crypt::encryptString($id));
        $this->authorize('update', $existing);

        $data = $request->validate([
            'amount'            => 'required|numeric|min:0.01',
            'payment_method'    => 'required|string',
            'payment_date'      => 'required|date',
            'payment_reference' => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);
        $purchase = $this->service->addPayment((int) $id, $data);

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'data'    => $purchase,
        ]);
    }
}
