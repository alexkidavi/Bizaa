<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops model_has_permissions / model_has_roles / role_has_permissions —
 * leftover spatie/laravel-permission pivot tables never used by the app
 * (it uses its own permission_role pivot + users.role_id instead).
 * Guarded with hasTable() so it's safe whether or not an environment's
 * `roles`/`permissions` tables were originally created by the old
 * (now-rewritten) Spatie-derived migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
    }

    public function down(): void
    {
        // Intentionally not recreated — these tables were unused dead weight.
    }
};
