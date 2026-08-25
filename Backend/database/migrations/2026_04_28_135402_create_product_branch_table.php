<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_branch', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            // STOCK PER BRANCH (VERY IMPORTANT FOR POS)
            $table->integer('stock')->default(0);

            // optional overrides per branch
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->decimal('buying_price', 12, 2)->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_branch');
    }
};