<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // "Monthly Plan"
            $table->string('slug')->unique();            // "monthly"
            $table->enum('billing_period', ['trial', 'monthly', 'quarterly', 'annually']);
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('duration_days');    // 14, 30, 90, 365
            $table->json('features')->nullable();        // ["Unlimited sales", "POS access", ...]
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
