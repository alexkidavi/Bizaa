<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpesa_stks', function (Blueprint $table) {
            // Nullable so existing sale STK rows are unaffected
            $table->unsignedBigInteger('subscription_plan_id')->nullable()->after('business_id');
            $table->index('subscription_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('mpesa_stks', function (Blueprint $table) {
            $table->dropIndex(['subscription_plan_id']);
            $table->dropColumn('subscription_plan_id');
        });
    }
};
