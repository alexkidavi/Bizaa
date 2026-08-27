<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Dashboard
            'dashboard.view',

            // Business
            'business.view',
            'business.update',
            'banks.manage',

            // Products
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.view_single',

            // Categories / Brands / Units
            'categories.manage',
            'brands.manage',
            'units.manage',

            // Branches
            'branches.view',
            'branches.create',
            'branches.update',
            'branches.delete',
            'branches.status_update',

            // Sales
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',
            'sales.record_payment',
            // Self-declaring an M-Pesa payment as received with no
            // Safaricom-side confirmation (manual "Confirm Paid" bypass,
            // or a freely-typed M-Pesa code) — not granted to Cashier by
            // default; a supervisor override for when STK polling/sync
            // genuinely can't verify a real payment.
            'sales.confirm_manual_payment',

            // Stock
            'stock.view',
            'stock.adjust',
            'stock.transfer',
            'stock.take',

            // Customers / Suppliers
            'customers.manage',
            'suppliers.manage',

            // Purchases
            'purchases.manage',
            'purchases.view_items',
            'purchases.view_payments',

            // Invoices / Quotations
            'invoices.manage',
            'quotations.manage',
            'quotations.convert',

            // Expenses
            'expenses.manage',
            'expense_categories.manage',

            // HR
            'hr.employees',
            'hr.awards',
            'hr.warnings',
            'hr.resignations',
            'hr.terminations',
            'hr.performance',
            'hr.departments',
            'hr.shifts',
            'hr.attendance',
            'hr.overtime',
            'hr.leaves',
            'hr.payroll',
            'hr.payroll.approve',
            'hr.payroll.pay',
            'hr.payroll_config',
            'hr.salary_advances',

            // Reports
            'reports.view',

            // Staff
            'staff.manage',
            'roles.view',
            'roles.manage',

            // MPESA
            'mpesa.pay',
            'mpesa.status',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'api']);
        $storeKeeper = Role::firstOrCreate(['name' => 'store_keeper', 'guard_name' => 'api']);
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'api']);
        $hr = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'api']);
        $rider = Role::firstOrCreate(['name' => 'rider', 'guard_name' => 'api']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN (ALL PERMISSIONS)
        |--------------------------------------------------------------------------
        */

        $admin->permissions()->sync(Permission::all());

        /*
        |--------------------------------------------------------------------------
        | MANAGER (MOST PERMISSIONS)
        |--------------------------------------------------------------------------
        */

        $manager->permissions()->sync(
            Permission::whereNotIn('name', [
                'roles.view',
                'roles.manage',
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | CASHIER
        |--------------------------------------------------------------------------
        */

        $cashier->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'sales.view',
                'sales.create',
                'customers.manage',
                'products.view',
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | STORE KEEPER
        |--------------------------------------------------------------------------
        */

        $storeKeeper->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'stock.view',
                'stock.adjust',
                'stock.transfer',
                'products.view',
                'purchases.manage',
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | ACCOUNTANT
        |--------------------------------------------------------------------------
        */

        $accountant->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'expenses.manage',
                'invoices.manage',
                'reports.view',
                'purchases.view_payments',
                'sales.record_payment',
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | HR
        |--------------------------------------------------------------------------
        */

        $hr->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'hr.employees',
                'hr.awards',
                'hr.warnings',
                'hr.resignations',
                'hr.terminations',
                'hr.performance',
                'hr.departments',
                'hr.shifts',
                'hr.attendance',
                'hr.overtime',
                'hr.leaves',
                'hr.payroll',
                'hr.payroll_config',
                'hr.salary_advances',
                'staff.manage',
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | RIDER (FUTURE DELIVERY SYSTEM)
        |--------------------------------------------------------------------------
        */

        $rider->permissions()->sync([
            Permission::where('name', 'dashboard.view')->first()->id,
        ]);
    }
}