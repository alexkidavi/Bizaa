<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Client-generated UUID, sent by the POS for EVERY sale (online
            // and offline). It's the idempotency key: the offline outbox
            // drainer may POST the same queued sale more than once (retry,
            // double-drain, flaky response), and createPosSale() returns the
            // already-created row instead of duplicating it when this matches.
            $table->uuid('client_sale_id')->nullable()->after('reference');

            // The sale was rung up while the device had no connection and
            // reached the server later via the outbox. Online sales keep the
            // default (false). Drives the stock policy below and the manager's
            // offline-sales / stock-variance report.
            $table->boolean('is_offline')->default(false)->after('client_sale_id');

            // The real wall-clock time the sale happened on the device.
            // `created_at` is when the row hit the server (== the sale time for
            // online sales, but minutes-to-hours later for a synced offline
            // one), so reports that care about "when did this sale occur" read
            // this when it's set.
            $table->timestamp('client_created_at')->nullable()->after('is_offline');

            // When an offline sale actually landed on the server. NULL for
            // online sales (they never went through the outbox).
            $table->timestamp('synced_at')->nullable()->after('client_created_at');

            // Per-line shortfall recorded when an offline sale drove branch
            // stock negative: [{product_id, requested, available}]. NULL when
            // the sale had enough stock (the normal case). Feeds the variance
            // report so a manager can reconcile after the fact.
            $table->json('stock_shortfall')->nullable()->after('synced_at');

            // business_id + client_sale_id is the real idempotency lookup
            // (client UUIDs are unique enough on their own, but the query is
            // always scoped by business).
            $table->unique(['business_id', 'client_sale_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'client_sale_id']);
            $table->dropColumn([
                'client_sale_id',
                'is_offline',
                'client_created_at',
                'synced_at',
                'stock_shortfall',
            ]);
        });
    }
};
