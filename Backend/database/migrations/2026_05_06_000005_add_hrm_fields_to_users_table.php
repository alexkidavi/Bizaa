<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('role_id')->constrained('departments')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->after('department_id')->constrained('shifts')->nullOnDelete();
            $table->decimal('basic_salary', 15, 2)->default(0)->after('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Department::class);
            $table->dropForeignIdFor(\App\Models\Shift::class);
            $table->dropColumn(['department_id', 'shift_id', 'basic_salary']);
        });
    }
};
