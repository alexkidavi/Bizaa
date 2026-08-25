<?php
namespace Database\Seeders;

use App\Models\County;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          $now = now();

        $roles = [
            [
                'name'        => 'Admin',
                // 'description' => 'Full system access. Can manage staff, settings, reports and all modules.',
                'guard_name' => 'api',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Cashier',
                // 'description' => 'Can process sales, record payments and issue receipts.',
                'guard_name' => 'api',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Accountant',
                // 'description' => 'Can view and manage financial records, invoices, purchases and reports.',
                'guard_name' => 'api',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Customer Service',
                // 'description' => 'Can manage customers, quotations and handle customer queries.',
                'guard_name' => 'api',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
