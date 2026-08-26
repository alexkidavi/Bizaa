<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Department;
use App\Models\EcommerceStore;
use App\Models\MpesaPayment;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\LeaveType;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Sale;
use App\Models\StockTake;

use App\Policies\BranchPolicy;
use App\Policies\BrandPolicy;
use App\Policies\BusinessPolicy;
use App\Policies\BusinessSubscriptionPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\EcommerceStorePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MpesaPaymentPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\QuotationPolicy;
use App\Policies\StaffPolicy;
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
use App\Policies\StockTakePolicy;

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
        StockTake::class      => StockTakePolicy::class,
        EcommerceStore::class => EcommerceStorePolicy::class,
        Purchase::class       => PurchasePolicy::class,
        Invoice::class        => InvoicePolicy::class,
        Quotation::class      => QuotationPolicy::class,
        User::class           => StaffPolicy::class,
        Business::class       => BusinessPolicy::class,
        MpesaPayment::class   => MpesaPaymentPolicy::class,
        BusinessSubscription::class => BusinessSubscriptionPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
