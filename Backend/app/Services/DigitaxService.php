<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockHistory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class DigitaxService
{
    /**
     * A business is only synced to DigiTax when the toggle is on AND an
     * API key has been saved — this is the single gate every write path
     * (product save, sale, stock adjust) must check before calling out.
     */
    public function isEnabled(?Business $business): bool
    {
        if (! $business) {
            info('DIGITAX SKIPPED: no business record');
            return false;
        }

        if (! $business->digitax_enabled) {
            info('DIGITAX SKIPPED: toggle is off', ['business_id' => $business->id]);
            return false;
        }

        if (! $business->digitax_api_key) {
            info('DIGITAX SKIPPED: no api key saved', ['business_id' => $business->id]);
            return false;
        }

        return true;
    }

    /**
     * Never log the raw key. Shows just enough to confirm which key is
     * active (e.g. to catch "saved the wrong one") without leaking it.
     */
    protected function maskKey(string $key): string
    {
        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 6) . '...' . substr($key, -4) . ' (' . strlen($key) . ' chars)';
    }

    protected function client(Business $business): PendingRequest
    {
        $baseUrl = config('services.digitax.base_url');

        info('DIGITAX REQUEST SETUP', [
            'business_id' => $business->id,
            'base_url' => $baseUrl,
            'environment' => $business->digitax_environment,
            'api_key' => $this->maskKey($business->digitax_api_key),
        ]);

        return Http::withHeaders([
            'X-API-Key' => $business->digitax_api_key,
            'Accept' => 'application/json',
        ])->baseUrl($baseUrl);
    }

    /**
     * VAT classification fallback when a product has no explicit override.
     * BrimPOS only models vat/non_vat locally, which can't distinguish
     * zero-rated/exempt/8% — item_class_code should be set per-product
     * for those cases via tax_type_code.
     */
    protected function resolveTaxTypeCode(Product $product): string
    {
        return $product->tax_type_code ?: ($product->tax_type === 'vat' ? 'B' : 'D');
    }

    protected function buildItemPayload(Product $product): array
    {
        $payload = [
            'item_class_code' => $product->item_class_code,
            'item_type_code' => $product->item_type_code ?: '2',
            'item_name' => $product->name,
            'origin_nation_code' => $product->origin_nation_code ?: 'KE',
            'package_unit_code' => $product->package_unit_code,
            'quantity_unit_code' => $product->quantity_unit_code,
            'tax_type_code' => $this->resolveTaxTypeCode($product),
            'default_unit_price' => (float) $product->selling_price,
        ];

        if ($product->barcode) {
            $payload['item_bar_code'] = $product->barcode;
        }

        if ($callbackUrl = config('services.digitax.callback_url')) {
            $payload['callback_url'] = $callbackUrl;
        }

        return $payload;
    }

    /**
     * Create or update the product's item record on DigiTax, depending on
     * whether it already has a digitax_item_id. Always persists the sync
     * outcome onto the product row — callers don't need to handle failure,
     * this never throws.
     */
    public function syncItem(Product $product): Product
    {
        info('DIGITAX SYNC ITEM: start', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'business_id' => $product->business_id,
            'existing_digitax_item_id' => $product->digitax_item_id,
        ]);

        $business = Business::find($product->business_id);

        if (! $this->isEnabled($business)) {
            // isEnabled() already logged the specific reason.
            return $product;
        }

        if (! $product->item_class_code || ! $product->package_unit_code || ! $product->quantity_unit_code) {
            info('DIGITAX SYNC ITEM: missing required codes, aborting', [
                'product_id' => $product->id,
                'item_class_code' => $product->item_class_code,
                'package_unit_code' => $product->package_unit_code,
                'quantity_unit_code' => $product->quantity_unit_code,
            ]);

            $product->forceFill([
                'digitax_status' => 'FAILED',
                'digitax_error' => 'Missing item_class_code, package_unit_code or quantity_unit_code.',
            ])->save();

            return $product;
        }

        $payload = $this->buildItemPayload($product);
        $isUpdate = (bool) $product->digitax_item_id;
        $endpoint = $isUpdate ? "/items/{$product->digitax_item_id}" : '/items';

        info('DIGITAX SYNC ITEM: sending request', [
            'product_id' => $product->id,
            'method' => $isUpdate ? 'PUT' : 'POST',
            'endpoint' => config('services.digitax.base_url') . $endpoint,
            'payload' => $payload,
        ]);

        try {
            $response = $isUpdate
                ? $this->client($business)->put($endpoint, $payload)
                : $this->client($business)->post($endpoint, $payload);

            info('DIGITAX SYNC ITEM: response received', [
                'product_id' => $product->id,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if ($response->successful()) {
                $body = $response->json();

                $product->forceFill([
                    'digitax_item_id' => $body['id'] ?? $product->digitax_item_id,
                    'etims_item_code' => $body['etims_item_code'] ?? $product->etims_item_code,
                    'digitax_status' => $body['status'] ?? 'PENDING',
                    'digitax_synced_at' => now(),
                    'digitax_error' => null,
                ])->save();

                info('DIGITAX SYNC ITEM: success', [
                    'product_id' => $product->id,
                    'digitax_item_id' => $product->digitax_item_id,
                    'etims_item_code' => $product->etims_item_code,
                    'digitax_status' => $product->digitax_status,
                ]);
            } else {
                $errorMessage = data_get($response->json(), 'message', $response->body());

                $product->forceFill([
                    'digitax_status' => 'FAILED',
                    'digitax_error' => $errorMessage,
                ])->save();

                info('DIGITAX SYNC ITEM: failed (non-2xx response)', [
                    'product_id' => $product->id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'full_body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $product->forceFill([
                'digitax_status' => 'FAILED',
                'digitax_error' => $e->getMessage(),
            ])->save();

            info('DIGITAX SYNC ITEM: exception thrown', [
                'product_id' => $product->id,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(10)->toArray(),
            ]);
        }

        return $product->fresh();
    }

    /**
     * UNCONFIRMED — pending DigiTax support reply (support@namiri.tech).
     * DigiTax's own docs never publish a payment_type_code table; this is
     * the standard KRA TIMS/eTIMS code set used across other OSCU
     * integrators, used here as a best-effort default. If DigiTax comes
     * back with different codes, this is the only place to change —
     * update the match() below, nothing else depends on the code values.
     */
    protected function resolvePaymentTypeCode(?string $method): string
    {
        return match ($method) {
            'cash' => '01',
            'credit' => '02',
            'card' => '05',
            'mpesa', 'mobile', 'mobile_money' => '06',
            'bank', 'cheque', 'check' => '04',
            default => '07', // other
        };
    }

    /**
     * Builds the eTIMS line-item payload for one sale item, using the
     * product's already-registered DigiTax item id. If the product hasn't
     * been synced yet (e.g. codes were added after the product was created),
     * this lazily syncs it first rather than failing the whole sale.
     */
    protected function buildSaleItemPayload($saleItem): ?array
    {
        $product = $saleItem->product;

        if (! $product) {
            info('DIGITAX SYNC SALE: sale item has no product, skipping item', [
                'sale_item_id' => $saleItem->id,
            ]);
            return null;
        }

        if (! $product->digitax_item_id) {
            info('DIGITAX SYNC SALE: product not yet registered on DigiTax, syncing now', [
                'product_id' => $product->id,
            ]);
            $product = $this->syncItem($product);
        }

        if (! $product->digitax_item_id) {
            info('DIGITAX SYNC SALE: product still unregistered after lazy sync, skipping item', [
                'product_id' => $product->id,
                'digitax_error' => $product->digitax_error,
            ]);
            return null;
        }

        $quantity = (float) $saleItem->quantity;
        $unitPrice = (float) $saleItem->unit_price;
        // DigiTax validates total_amount == quantity * unit_price exactly —
        // discounts must travel as a separate field, not be baked in here.
        $grossTotal = round($unitPrice * $quantity, 2);
        $discountRate = (float) ($saleItem->discount ?? 0);

        $payload = [
            'id' => $product->digitax_item_id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $grossTotal,
            'item_name' => $product->name,
            'item_class_code' => $product->item_class_code,
            'item_tax_type_code' => $this->resolveTaxTypeCode($product),
            // /sales-with-items requires this field even for products with
            // no real barcode — fall back to the internal product code.
            'item_bar_code' => $product->barcode ?: $product->product_code,
        ];

        if ($discountRate > 0) {
            // DigiTax wants a fraction (0–1), not a percentage — our own
            // SaleItem.discount column stores a percentage (e.g. 10 = 10%).
            $payload['discount_rate'] = round($discountRate / 100, 4);
            $payload['discount_amount'] = round($grossTotal * ($discountRate / 100), 2);
        }

        return $payload;
    }

    protected function buildSalePayload(Sale $sale, array $itemPayloads): array
    {
        $payload = [
            'sale_date' => $sale->created_at->format('Y-m-d'),
            'trader_invoice_number' => $sale->reference,
            'payment_type_code' => $this->resolvePaymentTypeCode($sale->payment_method),
            'invoice_status_code' => '01', // normal sale
            'items' => $itemPayloads,
        ];

        if ($sale->customer && $sale->customer->name) {
            $payload['customer_name'] = $sale->customer->name;
        }

        if ($callbackUrl = config('services.digitax.callback_url')) {
            $payload['callback_url'] = $callbackUrl;
        }

        return $payload;
    }

    /**
     * Submit a completed sale to DigiTax as an eTIMS invoice. Persists the
     * KRA receipt/serial number and etims_url (used to render the receipt's
     * verification QR code) onto the sale row. Never throws — failures are
     * recorded on the sale so checkout is never blocked by DigiTax being
     * slow or down.
     */
    public function syncSale(Sale $sale): Sale
    {
        info('DIGITAX SYNC SALE: start', [
            'sale_id' => $sale->id,
            'reference' => $sale->reference,
            'business_id' => $sale->business_id,
        ]);

        $business = Business::find($sale->business_id);

        if (! $this->isEnabled($business)) {
            // isEnabled() already logged the specific reason.
            return $sale;
        }

        $sale->loadMissing('items.product', 'customer');

        $itemPayloads = collect($sale->items)
            ->map(fn ($item) => $this->buildSaleItemPayload($item))
            ->filter()
            ->values()
            ->all();

        if (empty($itemPayloads)) {
            info('DIGITAX SYNC SALE: no syncable items, aborting', ['sale_id' => $sale->id]);

            $sale->forceFill([
                'digitax_status' => 'FAILED',
                'digitax_error' => 'None of the sale items are registered with DigiTax (missing classification codes on the product).',
            ])->save();

            return $sale;
        }

        $payload = $this->buildSalePayload($sale, $itemPayloads);

        info('DIGITAX SYNC SALE: sending request', [
            'sale_id' => $sale->id,
            'endpoint' => config('services.digitax.base_url') . '/sales-with-items',
            'payload' => $payload,
        ]);

        try {
            $response = $this->client($business)->post('/sales-with-items', $payload);

            info('DIGITAX SYNC SALE: response received', [
                'sale_id' => $sale->id,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if ($response->successful()) {
                $body = $response->json();

                $sale->forceFill([
                    'digitax_sale_id' => $body['id'] ?? null,
                    'kra_receipt_number' => $body['receipt_number'] ?? null,
                    'kra_invoice_number' => $body['invoice_number'] ?? null,
                    'kra_serial_number' => $body['serial_number'] ?? null,
                    'receipt_signature' => $body['receipt_signature'] ?? null,
                    // etims_url only populates once KRA finishes processing;
                    // offline_url is available immediately and is what the
                    // receipt QR should show until etims_url arrives via callback.
                    'etims_url' => $body['etims_url'] ?: null,
                    'offline_url' => $body['offline_url'] ?? null,
                    'digitax_status' => $body['status'] ?? 'PENDING',
                    'digitax_synced_at' => now(),
                    'digitax_error' => null,
                ])->save();

                info('DIGITAX SYNC SALE: success', [
                    'sale_id' => $sale->id,
                    'digitax_sale_id' => $sale->digitax_sale_id,
                    'kra_receipt_number' => $sale->kra_receipt_number,
                    'etims_url' => $sale->etims_url,
                    'offline_url' => $sale->offline_url,
                    'digitax_status' => $sale->digitax_status,
                ]);
            } else {
                $errorMessage = data_get($response->json(), 'message', $response->body());

                $sale->forceFill([
                    'digitax_status' => 'FAILED',
                    'digitax_error' => $errorMessage,
                ])->save();

                info('DIGITAX SYNC SALE: failed (non-2xx response)', [
                    'sale_id' => $sale->id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'full_body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $sale->forceFill([
                'digitax_status' => 'FAILED',
                'digitax_error' => $e->getMessage(),
            ])->save();

            info('DIGITAX SYNC SALE: exception thrown', [
                'sale_id' => $sale->id,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(10)->toArray(),
            ]);
        }

        return $sale->fresh();
    }

    /**
     * Push a stock movement to DigiTax's ledger for one item. Only called
     * for movements that don't already flow through syncItem() (initial
     * registration) or syncSale() (sale-driven decrements) — currently
     * that's manual stock adjustments. Purchases don't touch stock in this
     * app yet, and branch-to-branch transfers are a net-zero movement at
     * the business level (DigiTax's stock model has no branch concept —
     * /stock/adjust takes no branch parameter), so neither needs this.
     *
     * $delta is signed: positive = stock increased, negative = decreased.
     * A zero delta is a no-op and never reaches DigiTax.
     */
    /**
     * KRA eTIMS movement_type codes: 01-06 incoming, 11-16 outgoing.
     * Maps our local StockHistory.type to the closest official reason —
     * purchases are 02 (INCOMING PURCHASE), everything else falls back to
     * the generic adjustment codes (06/16).
     */
    protected function resolveMovementType(string $historyType, bool $isIncoming): string
    {
        if ($historyType === 'purchase' && $isIncoming) {
            return '02';
        }

        return $isIncoming ? '06' : '16';
    }

    public function syncStockAdjustment(Product $product, StockHistory $history, float $delta): StockHistory
    {
        info('DIGITAX SYNC STOCK: start', [
            'stock_history_id' => $history->id,
            'product_id' => $product->id,
            'delta' => $delta,
        ]);

        if ($delta == 0.0) {
            info('DIGITAX SYNC STOCK: zero delta, nothing to sync', ['stock_history_id' => $history->id]);
            $history->forceFill(['digitax_status' => 'SKIPPED'])->save();
            return $history;
        }

        $business = Business::find($product->business_id);

        if (! $this->isEnabled($business)) {
            // isEnabled() already logged the specific reason.
            return $history;
        }

        if (! $product->digitax_item_id) {
            info('DIGITAX SYNC STOCK: product not yet registered on DigiTax, syncing now', [
                'product_id' => $product->id,
            ]);
            $product = $this->syncItem($product);
        }

        if (! $product->digitax_item_id) {
            info('DIGITAX SYNC STOCK: product still unregistered after lazy sync, aborting', [
                'product_id' => $product->id,
                'digitax_error' => $product->digitax_error,
            ]);

            $history->forceFill([
                'digitax_status' => 'FAILED',
                'digitax_error' => 'Product is not registered with DigiTax: ' . ($product->digitax_error ?? 'unknown reason'),
            ])->save();

            return $history;
        }

        $isIncoming = $delta > 0;
        $payload = [
            'item_id' => $product->digitax_item_id,
            'quantity' => abs($delta),
            'action' => $isIncoming ? 'ADD' : 'DEDUCT',
            'movement_type' => $this->resolveMovementType($history->type, $isIncoming),
        ];

        info('DIGITAX SYNC STOCK: sending request', [
            'stock_history_id' => $history->id,
            'endpoint' => config('services.digitax.base_url') . '/stock/adjust',
            'payload' => $payload,
        ]);

        try {
            $response = $this->client($business)->put('/stock/adjust', $payload);

            info('DIGITAX SYNC STOCK: response received', [
                'stock_history_id' => $history->id,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if ($response->successful()) {
                $history->forceFill([
                    'digitax_status' => 'COMPLETE',
                    'digitax_synced_at' => now(),
                    'digitax_error' => null,
                ])->save();

                info('DIGITAX SYNC STOCK: success', ['stock_history_id' => $history->id]);
            } else {
                $errorMessage = data_get($response->json(), 'message', $response->body());

                $history->forceFill([
                    'digitax_status' => 'FAILED',
                    'digitax_error' => $errorMessage,
                ])->save();

                info('DIGITAX SYNC STOCK: failed (non-2xx response)', [
                    'stock_history_id' => $history->id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);
            }
        } catch (\Throwable $e) {
            $history->forceFill([
                'digitax_status' => 'FAILED',
                'digitax_error' => $e->getMessage(),
            ])->save();

            info('DIGITAX SYNC STOCK: exception thrown', [
                'stock_history_id' => $history->id,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $history->fresh();
    }
}
