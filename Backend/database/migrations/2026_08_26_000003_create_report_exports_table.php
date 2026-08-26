<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            // nullable + set null: who requested the export, not what it
            // belongs to — matches the pattern used for other owner-
            // attribution columns elsewhere in this schema.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_type');
            $table->string('format', 10); // xlsx | pdf
            $table->json('params')->nullable();
            $table->string('status', 20)->default('pending'); // pending|processing|completed|failed
            $table->string('file_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
