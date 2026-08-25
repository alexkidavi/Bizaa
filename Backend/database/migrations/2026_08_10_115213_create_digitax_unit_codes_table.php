<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digitax_unit_codes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['package', 'quantity']); // package_unit_code vs quantity_unit_code
            $table->string('code');
            $table->string('label');
            $table->timestamps();

            $table->unique(['type', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digitax_unit_codes');
    }
};
