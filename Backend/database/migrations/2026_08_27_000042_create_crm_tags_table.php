<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crm_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#229DE7'); // hex, for the UI badge
            $table->timestamps();

            $table->unique(['business_id', 'name']);
        });

        Schema::create('crm_contact_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('crm_contacts')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('crm_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contact_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_contact_tag');
        Schema::dropIfExists('crm_tags');
    }
};
