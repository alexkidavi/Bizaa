<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "source" started as a free-text string; replaced with a managed lookup
 * (crm_sources) so it's selected from a defined list instead of typed.
 * Safe to drop outright — no real contact data depends on the free-text
 * column yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->dropColumn('source');
        });
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->after('company_id')->constrained('crm_sources')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_id');
        });
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->string('source')->nullable()->after('company_id');
        });
    }
};
