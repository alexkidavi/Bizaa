<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CountiesTableSeeder::class,
            IndustriesTableSeeder::class,
            SubscriptionPlanSeeder::class,
            BusinessSeeder::class,
            UserSeeder::class,
            CategoriesTableSeeder::class,
            ProductSeeder::class,
            RoleSeeder::class,
            DigitaxUnitCodeSeeder::class,
            DigitaxItemClassSeeder::class,
        ]);
    }
}
