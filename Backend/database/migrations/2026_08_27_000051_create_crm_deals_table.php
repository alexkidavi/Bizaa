<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('crm_contacts')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('crm_companies')->nullOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('crm_pipeline_stages')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('value', 15, 2)->default(0);
            $table->decimal('probability', 5, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open'); // open, won, lost
            $table->text('lost_reason')->nullable();
            // Reuses the existing Quotation/Sale entities rather than
            // reinventing invoicing — set manually once the deal is
            // actually quoted / actually sold.
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_deals');
    }
};
