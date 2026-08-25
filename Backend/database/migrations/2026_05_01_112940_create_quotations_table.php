<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            // Linked invoice once converted
            $table->foreignId('invoice_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('quotation_number')->unique()->index();

            $table->enum('status', [
                'draft',
                'sent',
                'accepted',
                'rejected',
                'expired',
                'converted',
            ])->default('draft');

            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('currency', 3)->default('KES');
            $table->text('notes')->nullable();

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Useful indexes
            $table->index('customer_id');
            $table->index('status');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};