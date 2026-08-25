<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Available immediately on submit, unlike etims_url which only
            // populates once KRA finishes processing (can take a while).
            // Receipts should QR the offline_url first, upgrading to
            // etims_url once it's populated via the sale.sync callback.
            $table->string('offline_url')->nullable()->after('etims_url');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('offline_url');
        });
    }
};
