<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('image');
            $table->string('short_description')->nullable()->after('description');
            $table->decimal('weight', 8, 2)->nullable()->after('short_description');
            $table->json('tags')->nullable()->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'short_description', 'weight', 'tags']);
        });
    }
};
