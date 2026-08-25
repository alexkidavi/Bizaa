<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_take_items', function (Blueprint $table) {
            $table->id();
           $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('stock_take_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_id');

            $table->integer('recorded_stock'); // snapshot
            $table->integer('counted_stock')->nullable(); // optional (physical count)
            $table->integer('variance')->nullable(); // optional

            $table->timestamps();

            // 🔗 Foreign Keys
            $table->foreign('stock_take_id')
                  ->references('id')
                  ->on('stock_takes')
                  ->cascadeOnDelete();

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->cascadeOnDelete();

            $table->foreign('branch_id')
                  ->references('id')
                  ->on('branches')
                  ->cascadeOnDelete();

            // 🚀 Prevent duplicates per stock take
            $table->unique(['stock_take_id', 'product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_take_items');
    }
};
