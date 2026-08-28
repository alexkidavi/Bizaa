<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Crypt;

use App\Http\Controllers\{
    ProductController,
    BranchController,
    BrandController,
    BusinessController,
    BusinessOptionsController,
    CategoryController,
    LoginController,
    MpesaController,
    RegisterController,
    SaleController,
    StockController,
    UnitController,
    CustomerController,
    LoyaltyController,
    LoyaltySettingController,
    PurchaseController,
    SupplierController,
    InvoiceController,
    QuotationController,
    RoleController,
    PermissionController,
    StaffController,
    ReportController,
    ReportExportController,
    DashboardController,
    BankController,
    ExpenseCategoryController,
    ExpenseController,
    DepartmentController,
    EmployeeProfileController,
    AwardTypeController,
    AwardController,
    WarningTypeController,
    WarningController,
    ResignationController,
    TerminationTypeController,
    TerminationController,
    PerformanceCycleController,
    PerformanceRatingController,
    PerformanceReviewController,
    DigitaxController,
    ShiftController,
    AttendanceController,
    OvertimeController,
    LeaveTypeController,
    LeaveController,
    LeaveBalanceController,
    PayrollSalaryComponentController,
    PayrollTaxBandController,
    PayrollStatutoryRateController,
    PayrollSettingController,
    PayrollPeriodController,
    PayslipController,
    SalaryAdvanceController,
    CrmCompanyController,
    CrmContactController,
    CrmTagController,
    CrmSourceController,
    CrmPipelineStageController,
    CrmDealController,
    CrmActivityController,
    CrmCampaignController,
    CrmDashboardController,
    SubscriptionController,
    EcommerceStoreController,
    MarketplaceController
};

use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\BusinessController as SuperAdminBusiness;
use App\Http\Controllers\SuperAdmin\SubscriptionController as SuperAdminSubscription;
use App\Http\Controllers\SuperAdmin\StoreController as SuperAdminStore;
use App\Http\Controllers\SuperAdmin\ReportController as SuperAdminReport;

use App\Models\Business;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/counties', [BusinessOptionsController::class, 'getCounties']);
Route::get('/industries', [BusinessOptionsController::class, 'getIndustries']);

// Public landing/pricing page — LandingPricing.vue and Privacy.vue call
// this exact path (`/api/subscription-plans`, no auth) directly via a bare
// axios instance, not the app's authenticated `api` client. The only
// existing "subscription-plans" routes live under the auth:api-gated
// `/subscription` prefix (wrong path anyway) and under `/superadmin` —
// neither is reachable by a visitor who isn't logged in yet, which is
// exactly who the public pricing section is for.
Route::get('/subscription-plans', [SubscriptionController::class, 'allplans']);

// Public marketplace (no auth required)
Route::prefix('marketplace')->group(function () {
    Route::get('/stores',             [MarketplaceController::class, 'stores']);
    Route::get('/stores/{slug}',      [MarketplaceController::class, 'storeBySlug']);
    Route::get('/products/{id}',      [MarketplaceController::class, 'product']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login',    [LoginController::class, 'login']);
});

Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('/logout',          [LoginController::class, 'logout']);
    Route::post('/refresh',         [LoginController::class, 'refresh']);
    Route::post('/change-password', [LoginController::class, 'changePassword']);
    Route::post('/set-pin',         [LoginController::class, 'setPin']);
    Route::post('/remove-pin',      [LoginController::class, 'removePin']);
    Route::get('/verify-token',     [LoginController::class, 'verifyToken']);
    Route::patch('/profile',        [LoginController::class, 'updateProfile']);
});

/*
|--------------------------------------------------------------------------
| MPESA (public callback + authenticated STK)
|--------------------------------------------------------------------------
*/
Route::post('/mpesa/callback', [MpesaController::class, 'stkCallback']);

Route::middleware(['auth:api'])->prefix('mpesa')->group(function () {
    // POS checkout — frontend's actual call sites (MpesaModal.vue)
    Route::post('/stk',             [MpesaController::class, 'initiateStkPush']);
    Route::post('/stk-status',      [MpesaController::class, 'checkStatus']);
    Route::get('/recent-payments',  [MpesaController::class, 'recentPayments']);

    // Kept as aliases in case anything else still targets the old paths
    Route::post('/stk-push',    [MpesaController::class, 'initiateStkPush']);
    Route::get('/check-status', [MpesaController::class, 'checkStatus']);
});

/*
|--------------------------------------------------------------------------
| SUBSCRIPTION
|--------------------------------------------------------------------------
*/
Route::prefix('subscription')->middleware(['auth:api'])->group(function () {
    Route::get('/plans',        [SubscriptionController::class, 'plans']);
    Route::get('/current',      [SubscriptionController::class, 'current']);
    Route::get('/history',      [SubscriptionController::class, 'history']);
    Route::post('/initiate-stk',[SubscriptionController::class, 'initiateStk']);
    Route::get('/sub-plans',        [SubscriptionController::class, 'plans']);
    Route::get('/subscription-plans',        [SubscriptionController::class, 'allplans']);
});
/*
|--------------------------------------------------------------------------
| BRANCHES
|--------------------------------------------------------------------------
*/
Route::prefix('branches')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [BranchController::class, 'index'])
        ->middleware('permission:branches.view');

    Route::get('/get-connected', [BranchController::class, 'getConnected'])
        ->middleware('permission:branches.view');

    Route::post('/register', [BranchController::class, 'store'])
        ->middleware('permission:branches.create');

    Route::patch('/{id}/status', [BranchController::class, 'updateStatus'])
        ->middleware('permission:branches.status_update');

    Route::patch('/{id}', [BranchController::class, 'updateBranch'])
        ->middleware('permission:branches.update');

    Route::delete('/{id}/delete', [BranchController::class, 'destroy'])
        ->middleware('permission:branches.delete');
});

/*
|--------------------------------------------------------------------------
| BRANDS
|--------------------------------------------------------------------------
*/
Route::prefix('brands')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [BrandController::class, 'index'])
        ->middleware('permission:brands.manage');

    Route::post('/register', [BrandController::class, 'store'])
        ->middleware('permission:brands.manage');

    Route::patch('/{id}', [BrandController::class, 'updateBrand'])
        ->middleware('permission:brands.manage');

    Route::delete('/{id}/delete', [BrandController::class, 'destroy'])
        ->middleware('permission:brands.manage');
});

/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/
Route::prefix('categories')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [CategoryController::class, 'index'])
        ->middleware('permission:categories.manage');

    Route::post('/register', [CategoryController::class, 'store'])
        ->middleware('permission:categories.manage');

    Route::patch('/{id}', [CategoryController::class, 'updateCategory'])
        ->middleware('permission:categories.manage');

    Route::delete('/{id}/delete', [CategoryController::class, 'destroy'])
        ->middleware('permission:categories.manage');
});

/*
|--------------------------------------------------------------------------
| PRODUCTS
|--------------------------------------------------------------------------
*/
Route::prefix('products')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [ProductController::class, 'index'])
        ->middleware('permission:products.view');

    Route::get('/import/template', [ProductController::class, 'importTemplate'])
        ->middleware('permission:products.create');

    Route::post('/import', [ProductController::class, 'import'])
        ->middleware('permission:products.create');

    Route::get('/{id}', [ProductController::class, 'show'])
        ->middleware('permission:products.view_single');

    Route::post('/register', [ProductController::class, 'store'])
        ->middleware('permission:products.create');

    Route::patch('/{id}', [ProductController::class, 'updateProduct'])
        ->middleware('permission:products.update');

    Route::delete('/{id}/delete', [ProductController::class, 'destroy'])
        ->middleware('permission:products.delete');

    Route::post('/{id}/digitax-sync', [DigitaxController::class, 'syncItem'])
        ->middleware('permission:products.update');
});

/*
|--------------------------------------------------------------------------
| DIGITAX (KRA eTIMS integration — public callback + authenticated actions)
|--------------------------------------------------------------------------
*/
// Token in the path authenticates the caller — see services.digitax.callback_secret.
Route::post('/digitax/callback/{token?}', [DigitaxController::class, 'callback']);

Route::prefix('digitax')->middleware(['auth:api', 'subscription'])->group(function () {
    Route::get('/package-units', [DigitaxController::class, 'packageUnits']);
    Route::get('/quantity-units', [DigitaxController::class, 'quantityUnits']);
    Route::get('/item-classes', [DigitaxController::class, 'itemClasses']);
});

/*
|--------------------------------------------------------------------------
| STOCKS
|--------------------------------------------------------------------------
*/
Route::prefix('stocks')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [StockController::class, 'index'])
        ->middleware('permission:stock.view');

    Route::get('/product/{id}', [StockController::class, 'getByProduct'])
        ->middleware('permission:stock.view');
 Route::post('/save-counts/{id}', [StockController::class, 'saveCounts'])
        ->middleware('permission:stock.adjust');
    Route::post('/adjust/{id}', [StockController::class, 'adjustStock'])
        ->middleware('permission:stock.adjust');
    Route::get('/show/{id}', [StockController::class, 'show'])
        ->middleware('permission:stock.adjust');
        Route::get('/items/{id}', [StockController::class, 'getItems'])
        ->middleware('permission:stock.adjust');
    Route::post('/register', [StockController::class, 'stockTake'])
        ->middleware('permission:stock.take');

    Route::post('/transfer', [StockController::class, 'transferStock'])
        ->middleware('permission:stock.transfer');
         Route::get('/stock-transfers', [StockController::class, 'getTransfers'])
        ->middleware('permission:stock.transfer');
Route::get('branch-products', [StockController::class, 'branchProducts'])->middleware('permission:stock.transfer');
});

/*
|--------------------------------------------------------------------------
| SALES (POS CORE)
|--------------------------------------------------------------------------
*/
Route::prefix('sales')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get-sales', [SaleController::class, 'index'])
        ->middleware('permission:sales.view');

    Route::get('/{id}/get-sale', [SaleController::class, 'getById'])
        ->middleware('permission:sales.view');

    Route::post('/pos-sale', [SaleController::class, 'createPosSale'])
        ->middleware('permission:sales.create');

    Route::post('/{id}/record-payment', [SaleController::class, 'recordPayment'])
        ->middleware('permission:sales.record_payment');

    Route::post('/{id}/digitax-sync', [DigitaxController::class, 'syncSale'])
        ->middleware('permission:sales.view');

    Route::delete('/{id}/delete', [SaleController::class, 'destroy'])
        ->middleware('permission:sales.delete');
});

/*
|--------------------------------------------------------------------------
| LOYALTY PROGRAM
|--------------------------------------------------------------------------
*/
Route::prefix('loyalty')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('settings', [LoyaltySettingController::class, 'show'])
        ->middleware('permission:loyalty.manage');
    Route::put('settings', [LoyaltySettingController::class, 'update'])
        ->middleware('permission:loyalty.manage');

    Route::get('customers', [LoyaltyController::class, 'index'])
        ->middleware('permission:loyalty.manage');
    Route::get('customers/{id}/history', [LoyaltyController::class, 'history'])
        ->middleware('permission:loyalty.manage');
    Route::post('customers/{id}/adjust', [LoyaltyController::class, 'adjust'])
        ->middleware('permission:loyalty.manage');

    // Gated on sales.create (not loyalty.manage) — this is a checkout-time
    // check any cashier needs, not a management action.
    Route::post('preview-redeem', [LoyaltyController::class, 'previewRedeem'])
        ->middleware('permission:sales.create');
});

/*
|--------------------------------------------------------------------------
| PURCHASES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'subscription'])->group(function () {

    Route::apiResource('purchases', PurchaseController::class)
        ->middleware('permission:purchases.manage');

    Route::get('/purchases/{id}/items', [PurchaseController::class, 'items'])
        ->middleware('permission:purchases.manage');

    Route::get('/purchases/{id}/payments', [PurchaseController::class, 'payments'])
        ->middleware('permission:purchases.manage');

    Route::post('/purchases/{id}/record-payments', [PurchaseController::class, 'recordPayment'])
        ->middleware('permission:purchases.manage');
});

/*
|--------------------------------------------------------------------------
| CUSTOMERS
|--------------------------------------------------------------------------
*/
Route::prefix('expense-categories')
    ->middleware(['auth:api', 'subscription'])
    ->group(function () {

        Route::get('/', [ExpenseCategoryController::class, 'index'])
            ->middleware('permission:expenses.manage');

        Route::post('/', [ExpenseCategoryController::class, 'store'])
            ->middleware('permission:expenses.manage');

        Route::patch('/{id}', [ExpenseCategoryController::class, 'update'])
            ->middleware('permission:expenses.manage');

        Route::delete('/{id}', [ExpenseCategoryController::class, 'destroy'])
            ->middleware('permission:expenses.manage');
    });
    Route::prefix('expenses')
    ->middleware(['auth:api', 'subscription'])
    ->group(function () {

        // List expenses
        Route::get('/', [ExpenseController::class, 'index'])
            ->middleware('permission:expenses.manage');
Route::get('/summary', [ExpenseController::class, 'summary'])
            ->middleware('permission:expenses.manage');
        // Create expense
        Route::post('/', [ExpenseController::class, 'store'])
            ->middleware('permission:expenses.manage');

        // View single expense
        // Route::get('/{id}', [ExpenseController::class, 'show'])
        //     ->middleware('permission:expenses.manage');

        // Update expense
        Route::patch('/{id}', [ExpenseController::class, 'update'])
            ->middleware('permission:expenses.manage');

        // Delete expense
        Route::delete('/{id}', [ExpenseController::class, 'destroy'])
            ->middleware('permission:expenses.manage');

        // Payment ledger for a partially/unpaid expense
        Route::get('/{id}/payments', [ExpenseController::class, 'payments'])
            ->middleware('permission:expenses.manage');
        Route::post('/{id}/record-payments', [ExpenseController::class, 'recordPayment'])
            ->middleware('permission:expenses.manage');
    });
Route::prefix('customers')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [CustomerController::class, 'index'])
        ->middleware('permission:customers.manage');

    // Must stay ahead of the /{id} wildcard below — "pos-search" would
    // otherwise be captured as an {id} value.
    Route::get('/pos-search', [CustomerController::class, 'posSearch'])
        ->middleware('permission:customers.manage');

    Route::post('/register', [CustomerController::class, 'store'])
        ->middleware('permission:customers.manage');

    Route::patch('/{id}/update', [CustomerController::class, 'updateCustomer'])
        ->middleware('permission:customers.manage');

    Route::delete('/{id}/delete', [CustomerController::class, 'destroy'])
        ->middleware('permission:customers.manage');
         Route::get('/{id}', [CustomerController::class, 'show'])
        ->middleware('permission:customers.manage');
          Route::get('/{id}/sales', [CustomerController::class, 'getSales'])->middleware('permission:customers.manage');
    Route::get('/{id}/payments', [CustomerController::class, 'getPayments'])->middleware('permission:customers.manage');
});

/*
|--------------------------------------------------------------------------
| SUPPLIERS
|--------------------------------------------------------------------------
*/
Route::prefix('suppliers')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [SupplierController::class, 'index'])
        ->middleware('permission:suppliers.manage');

    Route::post('/register', [SupplierController::class, 'store'])
        ->middleware('permission:suppliers.manage');

    Route::delete('/{id}/delete', [SupplierController::class, 'destroy'])
        ->middleware('permission:suppliers.manage');

    Route::get('/{id}', [SupplierController::class, 'show'])
        ->middleware('permission:suppliers.manage');

    Route::patch('/{id}/update', [SupplierController::class, 'updatesupplier'])
        ->middleware('permission:suppliers.manage');

    Route::get('/{id}/sales', [SupplierController::class, 'getSales'])
        ->middleware('permission:suppliers.manage');

    Route::get('/{id}/payments', [SupplierController::class, 'getPayments'])
        ->middleware('permission:suppliers.manage');
});

/*
|--------------------------------------------------------------------------
| INVOICES / QUOTATIONS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'subscription'])->group(function () {

    Route::apiResource('invoices', InvoiceController::class)
        ->middleware('permission:invoices.manage');

    Route::apiResource('quotations', QuotationController::class)
        ->middleware('permission:quotations.manage');
});

/*
|--------------------------------------------------------------------------
| UNITS
|--------------------------------------------------------------------------
*/
Route::prefix('units')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [UnitController::class, 'index'])
        ->middleware('permission:units.manage');

    Route::post('/register', [UnitController::class, 'store'])
        ->middleware('permission:units.manage');

    Route::patch('/{id}', [UnitController::class, 'updateUnit'])
        ->middleware('permission:units.manage');

    Route::delete('/{id}/delete', [UnitController::class, 'destroy'])
        ->middleware('permission:units.manage');
});
Route::prefix('permissions')->middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/get', [PermissionController::class, 'index'])
        ->middleware('permission:roles.manage');
});
/*
|--------------------------------------------------------------------------
| STAFF + ROLES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'subscription'])->group(function () {

    Route::apiResource('staff', StaffController::class)
        ->middleware('permission:staff.manage');
    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view');
        Route::get('roles/{id}/permissions', [RoleController::class, 'getRolePermissions'])
        ->middleware('permission:roles.view');
        Route::post('roles', [RoleController::class, 'create'])
        ->middleware('permission:roles.view');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions'])
        ->middleware('permission:roles.manage');
        Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.manage');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.manage');
});

/*
|--------------------------------------------------------------------------
| REPORTS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'subscription'])->prefix('reports')->group(function () {

    Route::get('close-day', [ReportController::class, 'closeDayReport'])
        ->middleware('permission:reports.view');

    Route::get('items-sale', [ReportController::class, 'itemsSaleReport'])
        ->middleware('permission:reports.view');

    Route::get('sales-by-customer', [ReportController::class, 'salesByCustomer'])
        ->middleware('permission:reports.view');

    Route::get('sales-by-branch',    [ReportController::class, 'salesByBranch'])->middleware('permission:reports.view');
    Route::get('items-ledger',       [ReportController::class, 'itemsLedger'])->middleware('permission:reports.view');
    Route::get('customer-balances',  [ReportController::class, 'customerBalances'])->middleware('permission:reports.view');
    Route::get('supplier-balances',  [ReportController::class, 'supplierBalances'])->middleware('permission:reports.view');
        Route::get('sale-payments',     [ReportController::class, 'salePayments'])->middleware('permission:reports.view');
    Route::get('purchase-payments', [ReportController::class, 'purchasePayments'])->middleware('permission:reports.view');

    Route::get('general-sales',     [ReportController::class, 'generalSalesReport'])->middleware('permission:reports.view');
    Route::get('revenue-by-item',   [ReportController::class, 'revenueByItem'])->middleware('permission:reports.view');
    Route::get('items-purchase',    [ReportController::class, 'itemsPurchaseReport'])->middleware('permission:reports.view');
    Route::get('profit',            [ReportController::class, 'profitReport'])->middleware('permission:reports.view');
    Route::get('staff-sales',       [ReportController::class, 'staffSalesReport'])->middleware('permission:reports.view');
    Route::get('expenses',          [ReportController::class, 'expensesReport'])->middleware('permission:reports.view');
    Route::get('account-statement', [ReportController::class, 'accountStatement'])->middleware('permission:reports.view');

    // Server-side export (Excel/PDF), generated on the queue — see
    // GenerateReportExportJob. Same reports.view permission as viewing
    // the report itself.
    Route::post('export',              [ReportExportController::class, 'store'])->middleware('permission:reports.view');
    Route::get('export/{id}',          [ReportExportController::class, 'show'])->middleware('permission:reports.view');
    Route::get('export/{id}/download', [ReportExportController::class, 'download'])->middleware('permission:reports.view');
});

/*
|--------------------------------------------------------------------------
| HRM
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'subscription'])->prefix('hrm')->group(function () {

    Route::prefix('employees')->middleware('permission:hr.employees')->group(function () {
        Route::get('/', [EmployeeProfileController::class, 'index']);
        Route::post('/', [EmployeeProfileController::class, 'store']);
        Route::get('/{id}', [EmployeeProfileController::class, 'show']);
        Route::put('/{id}', [EmployeeProfileController::class, 'update']);
        Route::delete('/{id}', [EmployeeProfileController::class, 'destroy']);
        Route::patch('/{id}/status', [EmployeeProfileController::class, 'updateStatus']);
        Route::get('/{id}/history', [EmployeeProfileController::class, 'history']);
        Route::delete('/{id}/documents', [EmployeeProfileController::class, 'removeDocument']);
        Route::get('/{id}/documents/download', [EmployeeProfileController::class, 'downloadDocument']);
    });

    Route::apiResource('award-types', AwardTypeController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.awards');

    Route::apiResource('awards', AwardController::class)
        ->middleware('permission:hr.awards');
    Route::get('awards/{id}/document', [AwardController::class, 'downloadDocument'])
        ->middleware('permission:hr.awards');

    Route::apiResource('warning-types', WarningTypeController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.warnings');

    Route::apiResource('warnings', WarningController::class)
        ->middleware('permission:hr.warnings');
    Route::get('warnings/{id}/document', [WarningController::class, 'downloadDocument'])
        ->middleware('permission:hr.warnings');

    Route::apiResource('resignations', ResignationController::class)
        ->middleware('permission:hr.resignations');
    Route::patch('resignations/{id}/status', [ResignationController::class, 'updateStatus'])
        ->middleware('permission:hr.resignations');
    Route::get('resignations/{id}/document', [ResignationController::class, 'downloadDocument'])
        ->middleware('permission:hr.resignations');

    Route::apiResource('termination-types', TerminationTypeController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.terminations');

    Route::apiResource('terminations', TerminationController::class)
        ->middleware('permission:hr.terminations');
    Route::patch('terminations/{id}/status', [TerminationController::class, 'updateStatus'])
        ->middleware('permission:hr.terminations');
    Route::get('terminations/{id}/document', [TerminationController::class, 'downloadDocument'])
        ->middleware('permission:hr.terminations');

    Route::apiResource('performance-cycles', PerformanceCycleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.performance');

    Route::apiResource('performance-ratings', PerformanceRatingController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.performance');

    Route::apiResource('performance-reviews', PerformanceReviewController::class)
        ->middleware('permission:hr.performance');

    Route::apiResource('departments', DepartmentController::class)
        ->middleware('permission:hr.departments');

    Route::apiResource('shifts', ShiftController::class)
        ->middleware('permission:hr.shifts');

    Route::apiResource('attendance', AttendanceController::class)
        ->middleware('permission:hr.attendance');

    Route::apiResource('overtimes', OvertimeController::class)
        ->middleware('permission:hr.overtime');

    Route::apiResource('leave-types', LeaveTypeController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.leaves');

    Route::apiResource('leaves', LeaveController::class)
        ->middleware('permission:hr.leaves');
    Route::patch('leaves/{id}/status', [LeaveController::class, 'updateStatus'])
        ->middleware('permission:hr.leaves');
    Route::get('leaves/{id}/document', [LeaveController::class, 'downloadDocument'])
        ->middleware('permission:hr.leaves');

    Route::get('leave-balances/ensure', [LeaveBalanceController::class, 'ensureForYear'])
        ->middleware('permission:hr.leaves');
    Route::get('leave-balances', [LeaveBalanceController::class, 'index'])
        ->middleware('permission:hr.leaves');
    Route::get('leave-balances/{id}', [LeaveBalanceController::class, 'show'])
        ->middleware('permission:hr.leaves');
    Route::patch('leave-balances/{id}/adjust', [LeaveBalanceController::class, 'adjust'])
        ->middleware('permission:hr.leaves');
    Route::get('leave-balances/{id}/history', [LeaveBalanceController::class, 'history'])
        ->middleware('permission:hr.leaves');

    Route::apiResource('payroll-components', PayrollSalaryComponentController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.payroll_config');

    Route::apiResource('payroll-tax-bands', PayrollTaxBandController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.payroll_config');

    Route::apiResource('payroll-statutory-rates', PayrollStatutoryRateController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:hr.payroll_config');

    Route::get('payroll-settings', [PayrollSettingController::class, 'show'])
        ->middleware('permission:hr.payroll_config');
    Route::put('payroll-settings', [PayrollSettingController::class, 'update'])
        ->middleware('permission:hr.payroll_config');

    Route::apiResource('payroll-periods', PayrollPeriodController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->middleware('permission:hr.payroll');
    Route::post('payroll-periods/{id}/calculate', [PayrollPeriodController::class, 'calculate'])
        ->middleware('permission:hr.payroll');
    Route::patch('payroll-periods/{id}/cancel', [PayrollPeriodController::class, 'cancel'])
        ->middleware('permission:hr.payroll');
    Route::patch('payroll-periods/{id}/approve', [PayrollPeriodController::class, 'approve'])
        ->middleware('permission:hr.payroll.approve');
    Route::patch('payroll-periods/{id}/finalize', [PayrollPeriodController::class, 'finalize'])
        ->middleware('permission:hr.payroll.approve');
    Route::patch('payroll-periods/{id}/pay', [PayrollPeriodController::class, 'markPaid'])
        ->middleware('permission:hr.payroll.pay');
    Route::patch('payroll-periods/{id}/payslips/{payslipId}/exclude', [PayrollPeriodController::class, 'toggleExclude'])
        ->middleware('permission:hr.payroll');

    Route::get('payslips/{id}', [PayslipController::class, 'show'])
        ->middleware('permission:hr.payroll');
    Route::get('payslips/{id}/pdf', [PayslipController::class, 'pdf'])
        ->middleware('permission:hr.payroll');

    Route::apiResource('salary-advances', SalaryAdvanceController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->middleware('permission:hr.salary_advances');
    Route::patch('salary-advances/{id}/approve', [SalaryAdvanceController::class, 'approve'])
        ->middleware('permission:hr.salary_advances');
    Route::patch('salary-advances/{id}/reject', [SalaryAdvanceController::class, 'reject'])
        ->middleware('permission:hr.salary_advances');
    Route::patch('salary-advances/{id}/cancel', [SalaryAdvanceController::class, 'cancel'])
        ->middleware('permission:hr.salary_advances');
});
// verify-token moved into the auth group above
/*
|--------------------------------------------------------------------------
| CRM
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'subscription'])->prefix('crm')->group(function () {

    Route::apiResource('companies', CrmCompanyController::class)
        ->middleware('permission:crm.companies');

    Route::apiResource('tags', CrmTagController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:crm.tags');

    Route::apiResource('sources', CrmSourceController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:crm.sources');

    Route::apiResource('contacts', CrmContactController::class)
        ->middleware('permission:crm.contacts');
    Route::post('contacts/{id}/convert-to-customer', [CrmContactController::class, 'convertToCustomer'])
        ->middleware('permission:crm.contacts');

    Route::apiResource('pipeline-stages', CrmPipelineStageController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:crm.pipeline_settings');

    Route::apiResource('deals', CrmDealController::class)
        ->middleware('permission:crm.deals');
    Route::patch('deals/{id}/move-stage', [CrmDealController::class, 'moveStage'])
        ->middleware('permission:crm.deals');
    Route::patch('deals/{id}/mark-won', [CrmDealController::class, 'markWon'])
        ->middleware('permission:crm.deals');
    Route::patch('deals/{id}/mark-lost', [CrmDealController::class, 'markLost'])
        ->middleware('permission:crm.deals');
    Route::post('deals/{id}/link-quotation', [CrmDealController::class, 'linkQuotation'])
        ->middleware('permission:crm.deals');
    Route::post('deals/{id}/link-sale', [CrmDealController::class, 'linkSale'])
        ->middleware('permission:crm.deals');

    Route::apiResource('activities', CrmActivityController::class)
        ->middleware('permission:crm.activities');
    Route::patch('activities/{id}/complete', [CrmActivityController::class, 'complete'])
        ->middleware('permission:crm.activities');
    Route::patch('activities/{id}/reopen', [CrmActivityController::class, 'reopen'])
        ->middleware('permission:crm.activities');

    Route::apiResource('campaigns', CrmCampaignController::class)
        ->middleware('permission:crm.campaigns');
    Route::post('campaigns/preview-recipients', [CrmCampaignController::class, 'previewRecipients'])
        ->middleware('permission:crm.campaigns');
    Route::post('campaigns/{id}/send', [CrmCampaignController::class, 'send'])
        ->middleware('permission:crm.campaigns');

    Route::get('dashboard', [CrmDashboardController::class, 'index'])
        ->middleware('permission:crm.dashboard');
});
/*
|--------------------------------------------------------------------------
| DASHBOARD + BUSINESS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'subscription'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view');

    Route::get('/business', [BusinessController::class, 'show'])
        ->middleware('permission:business.view');

    Route::get('/business/secret-preview', [BusinessController::class, 'revealSecret'])
        ->middleware('permission:business.view');

    Route::post('/business/update', [BusinessController::class, 'update'])
        ->middleware('permission:business.update');

    Route::post('/business/tax', [BusinessController::class, 'updateTax'])
        ->middleware('permission:business.update');

    Route::post('/business/mpesa', [BusinessController::class, 'updateMpesa'])
        ->middleware('permission:business.update');

    Route::post('/business/digitax', [BusinessController::class, 'updateDigitax'])
        ->middleware('permission:business.update');

    Route::apiResource('banks', BankController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:banks.manage');
});
Route::middleware('auth:api')->get('/me', function (Request $request) {

    $user = $request->user()->load([
        'role.permissions'
    ]);

    $business = $user->business_id
        ? Business::find($user->business_id)
        : null;

    // FIX: admin override
    $permissions = collect();

    if ($user->role && $user->role->name === 'admin') {
        $permissions = \App\Models\Permission::pluck('name');
    } else {
        $permissions = $user->role?->permissions?->pluck('name') ?? collect();
    }

    return [
        'id'      => $user->id,
        'name'    => $user->name,
        'email'   => $user->email,
        'phone'   => $user->phone,
        'has_pin' => ! is_null($user->login_pin),

        'permissions' => $permissions->values()->all(),

        'business' => $business ? [
            'id'   => Crypt::encryptString($business->id),
            'name' => $business->business_name,
            'email' => $business->business_email,
            'phone'=> $business->business_phone,
            'location' => $business->business_location,
            'logo' => $business->business_logo
        ] : null,
    ];
});
/*
|--------------------------------------------------------------------------
| SUPER ADMIN (platform-level, is_superadmin flag required)
|--------------------------------------------------------------------------
*/
// Obfuscated superadmin identity check — name intentionally unrelated to superadmin
Route::middleware(['auth:api', 'superadmin'])->get('/who/check', fn() => response()->json(['ok' => true]));

Route::prefix('superadmin')->middleware(['auth:api', 'superadmin'])->group(function () {

    // Dashboard stats
    Route::get('/dashboard', [SuperAdminDashboard::class, 'index']);

    // Business management
    Route::get('/businesses',             [SuperAdminBusiness::class, 'index']);
    Route::get('/businesses/{id}',        [SuperAdminBusiness::class, 'show']);
    Route::post('/businesses/{id}/suspend',   [SuperAdminBusiness::class, 'suspend']);
    Route::post('/businesses/{id}/reinstate', [SuperAdminBusiness::class, 'reinstate']);

    // Subscription management
    Route::get('/subscriptions',              [SuperAdminSubscription::class, 'index']);
    Route::get('/subscription-plans',         [SuperAdminSubscription::class, 'plans']);
    Route::post('/subscriptions/grant',       [SuperAdminSubscription::class, 'grant']);
    Route::post('/subscriptions/{id}/revoke', [SuperAdminSubscription::class, 'revoke']);
    Route::post('/subscriptions/{id}/extend', [SuperAdminSubscription::class, 'extend']);

    // Store management
    Route::get('/stores',                        [SuperAdminStore::class, 'index']);
    Route::post('/stores/{id}/toggle-featured',  [SuperAdminStore::class, 'toggleFeatured']);
    Route::post('/stores/{id}/toggle-active',    [SuperAdminStore::class, 'toggleActive']);

    // Reports
    Route::get('/reports/overview', [SuperAdminReport::class, 'overview']);
});

/*
|--------------------------------------------------------------------------
| ECOMMERCE
|--------------------------------------------------------------------------
*/
Route::prefix('ecommerce')->middleware(['auth:api', 'subscription'])->group(function () {

    // Store CRUD
    Route::get('/stores',          [EcommerceStoreController::class, 'index'])
        ->middleware('permission:ecommerce.manage');

    Route::get('/stores/{id}',     [EcommerceStoreController::class, 'show'])
        ->middleware('permission:ecommerce.manage');

    Route::post('/stores',         [EcommerceStoreController::class, 'store'])
        ->middleware('permission:ecommerce.manage');

    Route::patch('/stores/{id}',   [EcommerceStoreController::class, 'update'])
        ->middleware('permission:ecommerce.manage');

    Route::delete('/stores/{id}',  [EcommerceStoreController::class, 'destroy'])
        ->middleware('permission:ecommerce.manage');

    // Product listing management
    Route::get('/stores/{id}/products',                                    [EcommerceStoreController::class, 'storeProducts'])
        ->middleware('permission:ecommerce.manage');

    Route::patch('/stores/{storeId}/products/{productBranchId}/listing',   [EcommerceStoreController::class, 'toggleListing'])
        ->middleware('permission:ecommerce.manage');

    Route::post('/stores/{storeId}/products/bulk-toggle',                  [EcommerceStoreController::class, 'bulkToggle'])
        ->middleware('permission:ecommerce.manage');
});
