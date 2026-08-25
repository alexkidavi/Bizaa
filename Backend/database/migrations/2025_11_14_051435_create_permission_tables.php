<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `roles` table. Originally this migration (and its
 * sibling `permissions`/`model_has_*` tables) came from the
 * spatie/laravel-permission package, which the app no longer uses —
 * `App\Models\Role`/`App\Models\Permission` are plain Eloquent models
 * backed by `roles`/`permissions`/`permission_role` instead. The
 * `permissions` table is now created solely by
 * 2026_05_06_164329_create_permissions_table (previously this file
 * created it too, colliding with that migration on a fresh install).
 * Later migrations (add_business_id_to_roles_table,
 * add_status_to_roles_table, update_roles_unique_constraint) extend
 * this base table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('guard_name', 100);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
