<?php

namespace App\Http\Controllers;

use App\Models\DigitaxItemClass;
use App\Models\DigitaxUnitCode;
use App\Models\Product;
use App\Models\Sale;
use App\Services\DigitaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitaxController extends Controller
{
    public function __construct(protected DigitaxService $service)
    {
    }

    /**
     * Reference lists for the product form's Packaging Unit / Quantity Unit
     * dropdowns — DigiTax's official fixed code tables, seeded locally so
     * the form never needs to hit DigiTax just to render options.
     */
    public function packageUnits(): JsonResponse
    {
        return response()->json(
            DigitaxUnitCode::package()->orderBy('code')->get(['code', 'label'])
        );
    }

    public function quantityUnits(): JsonResponse
    {
        return response()->json(
            DigitaxUnitCode::quantity()->orderBy('code')->get(['code', 'label'])
        );
    }

    /**
     * Search the KRA eTIMS item classification table (1000+ rows) for the
     * product form's classification picker modal. Requires at least 2
     * characters — this table is too large to browse unfiltered.
     */
    public function itemClasses(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $results = DigitaxItemClass::query()
            ->where('code', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%")
            ->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [$search])
            ->orderBy('level', 'desc')
            ->limit(50)
            ->get(['code', 'name', 'level']);

        return response()->json($results);
    }

    /**
     * Manually (re)push a single product to DigiTax as an eTIMS item.
     * Creates it if it has no digitax_item_id yet, otherwise updates it.
     */
    public function syncItem($id): JsonResponse
    {
        info('DIGITAX CONTROLLER: manual sync requested', ['product_id' => $id]);

        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $product = $this->service->syncItem($product);

        info('DIGITAX CONTROLLER: manual sync finished', [
            'product_id' => $product->id,
            'digitax_status' => $product->digitax_status,
            'digitax_error' => $product->digitax_error,
        ]);

        return response()->json([
            'message' => $product->digitax_status === 'FAILED'
                ? 'DigiTax sync failed.'
                : 'Product synced to DigiTax.',
            'data' => $product,
        ]);
    }

    /**
     * Manually (re)push a single sale to DigiTax as an eTIMS invoice.
     * Useful when the automatic sync at checkout failed (e.g. DigiTax was
     * briefly down) and needs a retry without re-ringing up the sale.
     */
    public function syncSale($id): JsonResponse
    {
        info('DIGITAX CONTROLLER: manual sale sync requested', ['sale_id' => $id]);

        $sale = Sale::findOrFail($id);
        $this->authorize('view', $sale);

        $sale = $this->service->syncSale($sale);

        info('DIGITAX CONTROLLER: manual sale sync finished', [
            'sale_id' => $sale->id,
            'digitax_status' => $sale->digitax_status,
            'digitax_error' => $sale->digitax_error,
        ]);

        return response()->json([
            'message' => $sale->digitax_status === 'FAILED'
                ? 'DigiTax sync failed.'
                : 'Sale synced to DigiTax.',
            'data' => $sale,
        ]);
    }

    /**
     * DigiTax calls this asynchronously once an item or sale it was sent
     * has finished processing on their side (event: item.sync | sale.sync).
     * Always acknowledge quickly — DigiTax retries on non-2xx.
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();
        info('DIGITAX CALLBACK: received', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        try {
            if ($event === 'item.sync' && ! empty($data['id'])) {
                $updated = Product::where('digitax_item_id', $data['id'])->update([
                    'etims_item_code' => $data['etims_item_code'] ?? null,
                    'digitax_status' => $data['status'] ?? 'COMPLETE',
                    'digitax_synced_at' => now(),
                    'digitax_error' => null,
                ]);

                info('DIGITAX CALLBACK: item.sync processed', [
                    'digitax_item_id' => $data['id'],
                    'rows_updated' => $updated,
                ]);
            } elseif ($event === 'sale.sync' && ! empty($data['id'])) {
                $updated = Sale::where('digitax_sale_id', $data['id'])->update([
                    'kra_receipt_number' => $data['receipt_number'] ?? null,
                    'kra_invoice_number' => $data['invoice_number'] ?? null,
                    'kra_serial_number' => $data['serial_number'] ?? null,
                    'receipt_signature' => $data['receipt_signature'] ?? null,
                    'etims_url' => $data['etims_url'] ?: null,
                    'digitax_status' => $data['status'] ?? 'COMPLETE',
                    'digitax_synced_at' => now(),
                    'digitax_error' => null,
                ]);

                info('DIGITAX CALLBACK: sale.sync processed', [
                    'digitax_sale_id' => $data['id'],
                    'rows_updated' => $updated,
                ]);
            } else {
                info('DIGITAX CALLBACK: unhandled or unrecognized event', [
                    'event' => $event,
                    'has_data_id' => ! empty($data['id']),
                ]);
            }
        } catch (\Throwable $e) {
            info('DIGITAX CALLBACK: exception thrown', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return response()->json(['received' => true]);
    }
}
