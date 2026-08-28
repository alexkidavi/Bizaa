<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Cached running balance, kept in sync with loyalty_point_transactions
            // under a row lock (same discipline as product_branch.stock — see
            // LoyaltyService) so it's always safe to read directly without
            // summing the ledger on every request.
            $table->unsignedInteger('loyalty_points_balance')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('loyalty_points_balance');
        });
    }
};
