<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digitax_item_classes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // itemClsCd
            $table->text('name');               // itemClsNm — some entries are long legal descriptions (600+ chars)
            $table->unsignedTinyInteger('level'); // itemClsLvl (1=broadest .. 4=most specific)
            $table->timestamps();

            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digitax_item_classes');
    }
};
