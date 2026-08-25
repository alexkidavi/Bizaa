<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('plan_id');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_method')->nullable();   // cash, mpesa, bank
            $table->string('payment_reference')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable(); // user_id who recorded payment
            $table->text('notes')->nullable();
            $table->timestamps();

            // $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->onDelete('restrict');
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_subscriptions');
    }
};
