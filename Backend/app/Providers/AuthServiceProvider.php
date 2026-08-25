<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Department;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\LeaveType;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Sale;

use App\Policies\BranchPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UnitPolicy;
use App\Policies\ExpenseCategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\ShiftPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\OvertimePolicy;
use App\Policies\LeaveTypePolicy;
use App\Policies\LeavePolicy;
use App\Policies\PayrollPolicy;
use App\Policies\RolePolicy;
use App\Policies\SalePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Branch::class         => BranchPolicy::class,
        Brand::class          => BrandPolicy::class,
        Category::class       => CategoryPolicy::class,
        Customer::class       => CustomerPolicy::class,
        Product::class        => ProductPolicy::class,
        Supplier::class       => SupplierPolicy::class,
        Unit::class           => UnitPolicy::class,
        ExpenseCategory::class => ExpenseCategoryPolicy::class,
        Expense::class        => ExpensePolicy::class,
        Department::class     => DepartmentPolicy::class,
        Shift::class          => ShiftPolicy::class,
        Attendance::class     => AttendancePolicy::class,
        Overtime::class       => OvertimePolicy::class,
        LeaveType::class      => LeaveTypePolicy::class,
        Leave::class          => LeavePolicy::class,
        Payroll::class        => PayrollPolicy::class,
        Role::class           => RolePolicy::class,
        Sale::class           => SalePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
