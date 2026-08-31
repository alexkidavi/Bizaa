<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Where the registration happened — 'web' today; a future mobile
        // app registration flow would pass 'mobile'. Deliberately nullable:
        // optional on the request, and every business row that existed
        // before this migration has no way to know its source, so NULL
        // ("unknown / pre-dates tracking") is the honest default rather
        // than guessing 'web' for historical rows.
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('source')->nullable()->after('business_location');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
