<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            // "Earn {earn_points_awarded} points per every {earn_amount_spent}
            // spent" — e.g. earn_amount_spent=100, earn_points_awarded=1 means
            // 1 point per KES 100. Kept as two plain numbers rather than one
            // combined rate so the settings form reads naturally.
            $table->decimal('earn_amount_spent', 15, 2)->default(100);
            $table->unsignedInteger('earn_points_awarded')->default(1);
            // Cash value of a single point when redeemed at POS.
            $table->decimal('point_value', 10, 4)->default(1);
            // A customer's points balance must be at least this much before
            // they're allowed to redeem anything at all (not a per-transaction
            // minimum — a balance threshold gate).
            $table->unsignedInteger('min_redeem_points')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_settings');
    }
};
