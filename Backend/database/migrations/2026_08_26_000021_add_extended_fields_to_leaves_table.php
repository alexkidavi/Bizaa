<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Nullable + backfilled rather than replacing user_id outright —
            // existing rows (and Attendance/Overtime/Payroll, which still key
            // off user_id) keep working; new balance/eligibility logic uses
            // this richer reference going forward.
            $table->foreignId('employee_id')->nullable()->after('user_id')
                ->constrained('employee_profiles')->nullOnDelete();
            $table->integer('working_days')->nullable()->after('days');
            $table->json('supporting_documents')->nullable()->after('reason');
        });

        DB::statement('
            UPDATE leaves
            INNER JOIN employee_profiles ON employee_profiles.user_id = leaves.user_id
            SET leaves.employee_id = employee_profiles.id
            WHERE leaves.employee_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropColumn(['working_days', 'supporting_documents']);
        });
    }
};
