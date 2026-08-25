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
       Schema::create('stock_histories', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('product_id');
    $table->unsignedBigInteger('branch_id');
    $table->unsignedBigInteger('business_id');
    $table->unsignedBigInteger('user_id');

    $table->enum('type', ['purchase', 'adjustment', 'transfer']);
    $table->enum('action', ['add', 'remove','set']);
   $table->string('reference')->unique();
    $table->integer('quantity');
    $table->text('notes')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
