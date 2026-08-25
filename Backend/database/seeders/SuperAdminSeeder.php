<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

/**
 * Seeds the superadmin role and its permissions.
 *
 * The role has business_id = NULL so it is invisible to all business-scoped
 * role listings. To make a user a super admin, manually set:
 *   UPDATE users SET is_superadmin = 1, business_id = NULL WHERE email = '...';
 *
 * Run: php artisan db:seed --class=SuperAdminSeeder
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |----------------------------------------------------------------------
        | Super Admin Permissions
        |----------------------------------------------------------------------
        | These are platform-level permissions, distinct from business-level
        | ones. They are prefixed with "superadmin." and stored in the same
        | permissions table but never assigned to business roles.
        */
        $superAdminPermissions = [
            'superadmin.dashboard',
            'superadmin.businesses.view',
            'superadmin.businesses.manage',
            'superadmin.subscriptions.view',
            'superadmin.subscriptions.manage',
            'superadmin.stores.view',
            'superadmin.stores.manage',
            'superadmin.reports.view',
        ];

        foreach ($superAdminPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        /*
        |----------------------------------------------------------------------
        | Super Admin Role  (business_id = NULL — invisible to businesses)
        |----------------------------------------------------------------------
        */
        $role = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'api'],
            ['business_id' => null,]
        );

        // Sync all superadmin permissions to the role
        $permIds = Permission::whereIn('name', $superAdminPermissions)->pluck('id');
        $role->permissions()->sync($permIds);

        $this->command->info('Super admin role and permissions seeded.');
        $this->command->info('');
        $this->command->warn('To grant super admin access to a user, run:');
        $this->command->info('  UPDATE users SET is_superadmin = 1, business_id = NULL, role_id = ' . $role->id . ' WHERE email = \'super@brimsoftsolutions.com\';');
        $this->command->info('');
        $this->command->warn('Or use the artisan command (if you create one):');
        $this->command->info('  php artisan superadmin:grant super@brimsoftsolutions.com');
    }
}
