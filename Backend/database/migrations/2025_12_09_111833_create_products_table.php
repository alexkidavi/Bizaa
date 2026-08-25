<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // BASIC INFO
            $table->string('name');
            $table->string('product_code')->unique();
            $table->string('barcode')->nullable();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // RELATIONS
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();

            // PRICING
            $table->decimal('buying_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('wholesale_price', 12, 2)->nullable();

            // TAX
            $table->enum('tax_type', ['vat', 'non_vat'])->default('vat');

            // INVENTORY
            $table->integer('opening_stock')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->integer('current_stock')->default(0);

            // SETTINGS
            $table->boolean('for_sale')->default(true);
            $table->boolean('for_purchase')->default(true);

            // EXTRA
            $table->string('color')->nullable();
            $table->string('image')->nullable();

            $table->timestamps();

            $table->index(['parent_id']);
            $table->index(['category_id']);
            $table->index(['brand_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
