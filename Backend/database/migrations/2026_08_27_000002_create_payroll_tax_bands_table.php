<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_tax_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            // A set of bands sharing the same effective_date form one PAYE
            // "version" — a new law change is added as a new effective_date
            // rather than mutating historical bands, so past payslips stay
            // auditable against the rules that produced them.
            $table->date('effective_date');
            $table->decimal('lower_limit', 15, 2);
            $table->decimal('upper_limit', 15, 2)->nullable(); // null = unbounded top band
            $table->decimal('rate', 5, 2); // percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_tax_bands');
    }
};
