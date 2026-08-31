<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Platform-level (not business_id-scoped) — the people who should
        // hear about every new business signing up, regardless of which
        // business they belong to. Managed from the super-admin Contacts
        // page; read by NewBusinessRegisteredJob whenever a business
        // registers (see AuthService::register()).
        Schema::create('superadmin_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('superadmin_contacts');
    }
};
