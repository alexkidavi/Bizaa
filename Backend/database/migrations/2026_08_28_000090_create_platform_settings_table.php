<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-row table (no scoping column — this is platform-wide, not
     * per-business) holding the site branding/contact info that used to
     * be hardcoded across LandingFooter.vue, Privacy.vue, and the welcome
     * email template. Editable only via the superadmin CRUD; read
     * publicly (unauthenticated) by the landing page and by backend
     * mailables that need to print a support contact.
     */
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Bizaa');
            $table->string('tagline')->nullable();
            $table->string('logo')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('address')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
