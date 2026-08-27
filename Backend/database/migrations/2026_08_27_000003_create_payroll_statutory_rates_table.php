<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_statutory_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->enum('scheme', ['nssf', 'shif', 'housing_levy']);
            // Same versioning approach as payroll_tax_bands: a set of rows
            // sharing scheme + effective_date is one version of that
            // scheme's rate table (NSSF is tiered; SHIF/Housing Levy are a
            // single unbounded band each).
            $table->date('effective_date');
            $table->decimal('lower_limit', 15, 2)->default(0);
            $table->decimal('upper_limit', 15, 2)->nullable();
            $table->decimal('employee_rate', 5, 2)->nullable();
            $table->decimal('employer_rate', 5, 2)->nullable();
            $table->decimal('minimum_amount', 15, 2)->nullable(); // e.g. SHIF's KES 300 floor
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_statutory_rates');
    }
};
