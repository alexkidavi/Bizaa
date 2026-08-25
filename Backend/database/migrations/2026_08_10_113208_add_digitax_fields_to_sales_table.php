<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('digitax_sale_id')->nullable()->after('total');
            $table->string('kra_receipt_number')->nullable()->after('digitax_sale_id');
            $table->string('kra_invoice_number')->nullable()->after('kra_receipt_number');
            $table->string('kra_serial_number')->nullable()->after('kra_invoice_number'); // KRA control unit serial (CU serial)
            $table->text('receipt_signature')->nullable()->after('kra_serial_number');
            $table->string('etims_url')->nullable()->after('receipt_signature'); // QR code encodes this — the KRA-hosted verification link for the receipt
            $table->string('digitax_status')->nullable()->after('etims_url'); // PENDING, COMPLETE, FAILED
            $table->text('digitax_error')->nullable()->after('digitax_status');
            $table->timestamp('digitax_synced_at')->nullable()->after('digitax_error');

            $table->index('digitax_sale_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'digitax_sale_id',
                'kra_receipt_number',
                'kra_invoice_number',
                'kra_serial_number',
                'receipt_signature',
                'etims_url',
                'digitax_status',
                'digitax_error',
                'digitax_synced_at',
            ]);
        });
    }
};
