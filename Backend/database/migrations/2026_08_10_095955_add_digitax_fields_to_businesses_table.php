<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->text('digitax_api_key')->nullable()->after('tax_api_key');
            $table->string('digitax_environment')->nullable()->default('sandbox')->after('digitax_api_key'); // sandbox | production
            // Master switch: when off, no eTIMS invoices are issued for this business's sales.
            $table->boolean('digitax_enabled')->default(false)->after('digitax_environment');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['digitax_api_key', 'digitax_environment', 'digitax_enabled']);
        });
    }
};
