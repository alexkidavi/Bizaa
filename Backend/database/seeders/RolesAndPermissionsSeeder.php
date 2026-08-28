<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
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

            // CRM
            'crm.contacts',
            'crm.companies',
            'crm.tags',
            'crm.sources',
            'crm.pipeline_settings',
            'crm.deals',
            'crm.activities',
            'crm.campaigns',
            'crm.dashboard',
            // Row-level ACL: without these, a user with the base crm.deals /
            // crm.contacts / crm.activities permission only sees records
            // assigned to them, not the whole business's — see CrmDealService
            // / CrmContactService / CrmActivityService / CrmDashboardService.
            'crm.deals.view_all',
            'crm.contacts.view_all',
            'crm.activities.view_all',

            // Loyalty program
            'loyalty.manage',

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
        | SYNC — every existing business's Admin role, always
        |--------------------------------------------------------------------------
        | AuthRepository::createAdminRoleWithAllPermissions() grants a new
        | business's "Admin" role every permission that exists *at signup
        | time*. Nothing re-runs that sync later — so a permission added to
        | the catalog above after a business registered never reaches that
        | business's Admin role on its own, and it silently can't access
        | the new feature. This closes that gap: every time this seeder
        | runs (which should happen on every deploy that adds a permission
        | — no separate manual step), every business's Admin role gets
        | topped up with whatever's new. syncWithoutDetaching() only adds
        | — it never removes a permission, so it can't undo anything a
        | business owner customized. Deliberately scoped to roles named
        | exactly "Admin" (the auto-provisioned one) — a business's own
        | custom Manager/Cashier/etc. roles are curated by them and must
        | never be silently expanded.
        */
        $allPermissionIds = Permission::pluck('id');
        Role::where('name', 'Admin')->get()->each(
            fn (Role $admin) => $admin->permissions()->syncWithoutDetaching($allPermissionIds)
        );

        /*
        |--------------------------------------------------------------------------
        | ROLES — demo/dev bootstrap only, never production
        |--------------------------------------------------------------------------
        | This seeds a full sample role catalog (admin/manager/cashier/...)
        | for ONE business, so local dev/tests have something realistic to
        | log in as. It is NOT how real businesses get their roles — that
        | happens at signup via AuthRepository::createAdminRoleWithAllPermissions()
        | (which correctly sets business_id) and afterwards via the Roles UI.
        | `roles.business_id` is a required FK (see
        | 2026_05_06_174852_add_business_id_to_roles_table.php), so unlike
        | the global Permission catalog above, these rows can't safely be
        | created without a business to attach to — running this against
        | production would either fail outright (no business row for a
        | truly fresh install) or silently graft this demo catalog onto a
        | real customer's business. Bail out in both cases: the permission
        | catalog above still seeds fine on its own (that's the part that
        | must run in production whenever a new permission is added).
        */
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $business = Business::first();
        if (! $business) {
            return;
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'business_id' => $business->id, 'guard_name' => 'api']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'business_id' => $business->id, 'guard_name' => 'api']);
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'business_id' => $business->id, 'guard_name' => 'api']);
        $storeKeeper = Role::firstOrCreate(['name' => 'store_keeper', 'business_id' => $business->id, 'guard_name' => 'api']);
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'business_id' => $business->id, 'guard_name' => 'api']);
        $hr = Role::firstOrCreate(['name' => 'hr', 'business_id' => $business->id, 'guard_name' => 'api']);
        $rider = Role::firstOrCreate(['name' => 'rider', 'business_id' => $business->id, 'guard_name' => 'api']);

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