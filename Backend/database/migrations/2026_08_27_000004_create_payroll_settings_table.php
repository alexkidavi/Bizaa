<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
            $table->enum('pay_frequency', ['monthly', 'biweekly', 'weekly'])->default('monthly');
            $table->unsignedTinyInteger('working_days_per_month')->default(22);
            $table->decimal('standard_hours_per_day', 4, 2)->default(8);
            $table->decimal('overtime_multiplier', 4, 2)->default(1.5);
            $table->enum('rounding_mode', ['none', 'nearest', 'up', 'down'])->default('nearest');
            $table->enum('proration_method', ['calendar_days', 'working_days'])->default('calendar_days');
            $table->decimal('personal_relief', 10, 2)->default(2400); // PAYE monthly personal relief
            $table->string('currency', 5)->default('KES');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
