<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * users.role (string, default 'user') and users.role_id (the real RBAC
 * foreign key, resolved via User::role()) have coexisted since the
 * original users table migration — and because they share the exact
 * name "role", Eloquent's attribute lookup always resolves $user->role
 * to the raw string column, never to the role() relationship: a real
 * database column always wins over a same-named relationship method.
 *
 * Every call site in this codebase (PermissionMiddleware, User::
 * hasPermission()/getAllPermissionsList(), the /me route, SuperAdmin's
 * business listing) uses $user->role expecting the Role model — none of
 * them expect the string — so every one of those has been throwing
 * "Attempt to read property \"name\" on string" the moment it runs.
 * Nothing in the app ever writes to the string column either (it's
 * absent from User::$fillable and no create/update call sets it) —
 * it just sits at its default forever. Dropping it removes the
 * shadowing at the source rather than working around it at every call
 * site.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user');
            });
        }
    }
};
