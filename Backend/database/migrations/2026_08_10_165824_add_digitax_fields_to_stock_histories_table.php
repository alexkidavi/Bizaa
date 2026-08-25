<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->string('digitax_status')->nullable()->after('notes'); // SKIPPED, PENDING, FAILED
            $table->text('digitax_error')->nullable()->after('digitax_status');
            $table->timestamp('digitax_synced_at')->nullable()->after('digitax_error');
        });
    }

    public function down(): void
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->dropColumn(['digitax_status', 'digitax_error', 'digitax_synced_at']);
        });
    }
};
