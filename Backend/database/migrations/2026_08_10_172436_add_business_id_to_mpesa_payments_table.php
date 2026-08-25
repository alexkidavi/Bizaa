<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpesa_payments', function (Blueprint $table) {
            // Nullable: the callback always sets it going forward (resolved
            // from the originating MpesaStk row), but existing rows predate this.
            $table->foreignId('business_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mpesa_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
        });
    }
};
