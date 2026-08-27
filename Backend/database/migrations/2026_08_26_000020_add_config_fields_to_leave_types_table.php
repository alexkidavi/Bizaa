<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('accrual_rule')->default('none')->after('days_allowed'); // none, monthly, annual
            $table->decimal('accrual_rate', 5, 2)->nullable()->after('accrual_rule'); // days accrued per accrual period
            $table->boolean('carry_forward_allowed')->default(false)->after('accrual_rate');
            $table->integer('max_carry_forward_days')->nullable()->after('carry_forward_allowed');
            $table->string('gender_eligibility')->nullable()->after('max_carry_forward_days'); // male, female, null = anyone
            $table->boolean('requires_documentation')->default(false)->after('gender_eligibility');
            $table->boolean('requires_approval')->default(true)->after('requires_documentation');
            $table->boolean('is_paid')->default(true)->after('requires_approval');
            $table->boolean('is_active')->default(true)->after('is_paid');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn([
                'accrual_rule', 'accrual_rate', 'carry_forward_allowed', 'max_carry_forward_days',
                'gender_eligibility', 'requires_documentation', 'requires_approval', 'is_paid', 'is_active',
            ]);
        });
    }
};
