<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_stores', function (Blueprint $table) {
            $table->id();
             $table->foreignId('business_id');
            $table->foreignId('branch_id')->unique();
            $table->foreignId('user_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('banner_color', 20)->nullable()->default('#1e3a5f');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_stores');
    }
};
