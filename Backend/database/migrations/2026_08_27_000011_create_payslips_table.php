<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employee_profiles')->cascadeOnDelete();

            // Snapshots at calculation time — later edits to the employee's
            // basic_salary or company config must not retroactively change
            // an already-generated payslip.
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->integer('working_days')->default(0); // period's working days
            $table->integer('days_paid')->default(0); // after proration
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);

            $table->decimal('gross_earnings', 15, 2)->default(0);
            $table->decimal('taxable_income', 15, 2)->default(0);
            $table->decimal('paye', 15, 2)->default(0);
            $table->decimal('nssf_employee', 15, 2)->default(0);
            $table->decimal('nssf_employer', 15, 2)->default(0);
            $table->decimal('shif', 15, 2)->default(0);
            $table->decimal('housing_levy_employee', 15, 2)->default(0);
            $table->decimal('housing_levy_employer', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0); // custom deduction components
            $table->decimal('advance_repayment', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->default(0);

            $table->boolean('is_excluded')->default(false); // held back from this batch
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
