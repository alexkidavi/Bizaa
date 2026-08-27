<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->foreignId('termination_type_id')->constrained('termination_types')->cascadeOnDelete();
            $table->date('termination_date');
            $table->date('effective_date');
            $table->string('reason');
            $table->text('detailed_explanation')->nullable();
            $table->boolean('notice_given')->default(false);
            $table->integer('notice_period_days')->nullable();
            $table->text('hr_comments')->nullable();
            $table->text('manager_comments')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->decimal('final_settlement_amount', 15, 2)->nullable();
            $table->text('final_settlement_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminations');
    }
};
