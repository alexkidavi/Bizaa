<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payslip_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained('payslips')->cascadeOnDelete();
            $table->enum('type', ['earning', 'deduction']);
            // Where the line came from — lets a payslip show a full,
            // auditable breakdown (per-line) instead of just totals.
            $table->enum('source', ['basic', 'component', 'overtime', 'statutory', 'advance'])->default('component');
            $table->string('name'); // snapshot label, e.g. "House Allowance", "PAYE"
            $table->string('code')->nullable(); // snapshot component code / statutory scheme
            $table->decimal('amount', 15, 2); // always positive; sign implied by `type`
            $table->json('meta')->nullable(); // e.g. {"rate": 15, "hours": 6}
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_lines');
    }
};
