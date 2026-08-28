<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            // Set when this row came from a sale (earn on purchase, or
            // redeem as payment) — null for a manual staff adjustment.
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->enum('type', ['earn', 'redeem', 'adjustment']);
            // Signed: positive for earn/credit-adjustment, negative for
            // redeem/debit-adjustment.
            $table->integer('points');
            // Running balance snapshot at the moment this row was written,
            // computed under the same lock as the customers.loyalty_points_balance
            // update — makes history display a simple ordered read, no
            // running-sum computation needed.
            $table->unsignedInteger('balance_after');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_point_transactions');
    }
};
