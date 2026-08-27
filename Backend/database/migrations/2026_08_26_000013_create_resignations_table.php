<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resignations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->date('resignation_date');
            $table->date('notice_date')->nullable();
            $table->integer('notice_period_days')->nullable();
            $table->date('expected_last_working_day')->nullable();
            $table->date('actual_last_working_day')->nullable();
            $table->text('reason')->nullable();
            $table->text('employee_comments')->nullable();
            $table->text('hr_comments')->nullable();
            $table->text('manager_comments')->nullable();
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected', 'completed', 'cancelled'])->default('submitted');
            $table->json('supporting_documents')->nullable();
            $table->text('exit_interview_notes')->nullable();
            $table->foreignId('exit_interview_conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('exit_interview_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resignations');
    }
};
