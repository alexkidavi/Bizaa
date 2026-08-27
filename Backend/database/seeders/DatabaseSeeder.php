<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * `php artisan db:seed` needs to be safe to run on a fresh production
     * install, not just locally — but `CategoriesTableSeeder` and
     * `ProductSeeder` create fake demo content (sample categories and
     * products) that has no business being anywhere near a real
     * customer's database. Only the first group below is real,
     * environment-agnostic data that every install — dev or production —
     * genuinely needs; the second group is demo/dev bootstrap only and is
     * skipped outside local/testing.
     *
     * There is no seeder here that creates a Business or User — that's
     * deliberate. Businesses come from the real signup flow
     * (AuthRepository::createAdminRoleWithAllPermissions() builds the
     * correctly business-scoped Admin role at registration time); for
     * local dev, create yours the same way or by hand before running the
     * demo group below, since CategoriesTableSeeder/ProductSeeder attach
     * their sample data to whatever Business::first() finds.
     *
     * @return void
     */
    public function run()
    {
        // Real reference data — every environment needs this, all idempotent.
        $this->call([
            CountiesTableSeeder::class,
            IndustriesTableSeeder::class,
            SubscriptionPlanSeeder::class,
            DigitaxUnitCodeSeeder::class,
            DigitaxItemClassSeeder::class,
        ]);

        // Global Permission catalog — a newly-registered business's Admin
        // role is built from this list (AuthRepository::createAdminRoleWithAllPermissions()),
        // so it must exist before any real business can sign up. This is
        // safe to run unconditionally: RolesAndPermissionsSeeder only
        // seeds Permission rows here (no business_id dependency); its
        // role-catalog half is separately gated to local/testing inside
        // the seeder itself, since roles.business_id is a required FK.
        $this->call(RolesAndPermissionsSeeder::class);

        // Demo/dev bootstrap only — sample categories + products attached
        // to whichever Business already exists. Never runs against
        // production, and does nothing useful locally until you've
        // created a business (via the real signup flow, or by hand).
        if (app()->environment('local', 'testing')) {
            $this->call([
                CategoriesTableSeeder::class,
                ProductSeeder::class,
            ]);

            // Seeds the sample role catalog (admin/manager/cashier/...)
            // against whatever Business now exists.
            $this->call(RolesAndPermissionsSeeder::class);
        }
    }
}
