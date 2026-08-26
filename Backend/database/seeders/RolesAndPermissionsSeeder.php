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
            'hr.departments',
            'hr.shifts',
            'hr.attendance',
            'hr.overtime',
            'hr.leaves',
            'hr.payroll',

            // Reports
            'reports.view',

            // Staff
            'staff.manage',
            'roles.view',

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
                'roles.view'
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
                'hr.departments',
                'hr.shifts',
                'hr.attendance',
                'hr.overtime',
                'hr.leaves',
                'hr.payroll',
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