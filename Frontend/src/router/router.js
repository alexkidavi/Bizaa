// src/router/index.js
import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

// Layouts
import DashboardLayout from "@/layouts/DashboardLayout.vue";

// Pages
import PrivacyPage from "@/pages/Landing/Privacy.vue";
import LandingPage from "@/pages/Landing/LandingPage.vue";
import LoginPage from "@/pages/Auth/Login.vue";
import RegisterPage from "@/pages/Auth/Register.vue";
import Dashboard from "@/pages/Dashboard/Dashboard.vue";

import ProductsList from "@/pages/Products/Items/List.vue";
import addProduct from "@/pages/Products/Items/addProduct.vue";
import ViewProduct from "@/pages/Products/Items/viewProduct.vue";

import UnitsList from "@/pages/Products/Units/List.vue";
import CategoriesList from "@/pages/Products/Categories/List.vue";
import BrandsList from "@/pages/Products/Brands/List.vue";

import StockTake from "@/pages/Stock/StockTake.vue";
import ViewStock from "@/pages/Stock/ViewStock.vue";
import StockTranfer from "@/pages/Stock/StockTranfer.vue";

import POS from "@/pages/Sales/POS.vue";
import SalesList from "@/pages/Sales/List.vue";
import ViewSale from "@/pages/Sales/ViewSale.vue";
import Customers from "@/pages/Customers/List.vue";
import ViewCustomer from "@/pages/Customers/ViewCustomer.vue";  
import SuppliersList from "@/pages/Suppliers/List.vue";
import ViewSupplier from "@/pages/Suppliers/ViewSupplier.vue";
import StaffList from "@/pages/Staff/StaffList.vue";
import Branches from "@/pages/Branches/List.vue";

import Settings from "@/pages/Setting/Settings.vue";

import GeneralReport from "@/pages/Reports/Reports.vue";
import Transactions from "@/pages/Reports/Transactions.vue";
import RoleList from "@/pages/Staff/RoleList.vue";
import SubscriptionPage from "@/pages/Subscription/SubscriptionPage.vue";
import SupportPage from "@/pages/Support/SupportPage.vue";
const routes = [
  { path: "/", component: LandingPage },
  { path: "/privacy", component: PrivacyPage },
  { path: "/login", component: LoginPage },
  { path: "/register", component: RegisterPage },

  // SUPER ADMIN (only accessible when is_superadmin = true)
  {
    path: "/superadmin",
    component: () => import("@/layouts/SuperAdminLayout.vue"),
    meta: { superAdmin: true },
    children: [
      { path: "",        redirect: "/superadmin/dashboard" },
      { path: "dashboard",      name: "SuperAdminDashboard",     component: () => import("@/pages/SuperAdmin/Dashboard.vue") },
      { path: "businesses",     name: "SuperAdminBusinesses",    component: () => import("@/pages/SuperAdmin/Businesses.vue") },
      { path: "businesses/:id", name: "SuperAdminBusinessView",  component: () => import("@/pages/SuperAdmin/BusinessView.vue") },
      { path: "subscriptions",  name: "SuperAdminSubscriptions", component: () => import("@/pages/SuperAdmin/Subscriptions.vue") },
      { path: "plans",          name: "SuperAdminPlans",         component: () => import("@/pages/SuperAdmin/SubscriptionPlans.vue") },
      { path: "stores",         name: "SuperAdminStores",        component: () => import("@/pages/SuperAdmin/Stores.vue") },
      { path: "reports",        name: "SuperAdminReports",       component: () => import("@/pages/SuperAdmin/Reports.vue") },
      { path: "settings",       name: "SuperAdminSettings",      component: () => import("@/pages/SuperAdmin/Settings.vue") },
    ],
  },
  { path: "/marketplace", component: () => import("@/pages/Marketplace/MarketplacePage.vue"), name: "marketplace", meta: { public: true } },
  { path: "/marketplace/store/:slug", component: () => import("@/pages/Marketplace/StorePage.vue"), name: "store-page", meta: { public: true } },
  { path: "/marketplace/product/:id", component: () => import("@/pages/Marketplace/ProductDetailsPage.vue"), name: "product-details", meta: { public: true } },

  {
    path: "/dashboard",
    component: DashboardLayout,
    children: [{ path: "", component: Dashboard }],
  },

  // PRODUCTS
  {
    path: "/products",
    component: DashboardLayout,
    children: [
      { path: "list", component: ProductsList, meta: { permission: "products.view" } },
      { path: "add", component: addProduct, meta: { permission: "products.create" } },
      { path: ":id", component: ViewProduct, meta: { permission: "products.view" } },
      { path: "units", component: UnitsList, meta: { permission: "units.manage" } },
      { path: "categories", component: CategoriesList, meta: { permission: "categories.manage" } },
      { path: "brands", component: BrandsList, meta: { permission: "brands.manage" } },
      {
        path: 'edit/:id',
        component: addProduct,
        meta: { permission: 'products.view' }
      },
    ],
  },

  // STOCK
  {
    path: "/stock",
    component: DashboardLayout,
    children: [
      { path: "take", component: StockTake, meta: { permission: "stock.take" } },
       { path: "take/:id", component: ViewStock, meta: { permission: "stock.take" } },
      { path: "transfer", component: StockTranfer, meta: { permission: "stock.transfer" } },
    ],
  },

  // SALES
  {
    path: "/sales",
    component: DashboardLayout,
    children: [
      { path: "list", component: SalesList, meta: { permission: "sales.view" } },
      { path: ":id", component: ViewSale, props: true, meta: { permission: "sales.view" } },
    ],
  },

  { path: "/sales/pos", component: POS, meta: { permission: "sales.create" } },

  // CUSTOMERS
  {
    path: "/customers/list",
    component: DashboardLayout,
    
    children: [
      { path: "", component: Customers, meta: { permission: "customers.manage" } },
       {
      path: ":id",
      name: "ViewCustomer",
      component: ViewCustomer,
      meta: { permission: "customers.manage" }
    },
    ],
  },
  {
    path: '/purchases',
    component: DashboardLayout,
    children: [
      {
        path: 'list',
        name: 'PurchasesList',
        component: () => import('@/pages/Purchases/PurchasesList.vue'),
        meta: { permission: 'purchases.manage' }
      },
      {
        path: 'new',
        name: 'NewPurchase',
        component: () => import('@/pages/Purchases/NewPurchase.vue'),
        meta: { permission: 'purchases.view_items' }
      },
      {
        path: ':id',
        name: 'ViewPurchase',
        component: () => import('@/pages/Purchases/ViewPurchase.vue'),
        meta: { permission: 'purchases.view_items' }
      },
      {
        path: ':id/edit',
        name: 'EditPurchase',
        component: () => import('@/pages/Purchases/NewPurchase.vue'),
        meta: { permission: 'purchases.view_items' }
      },
      {
        path: ':id/documents',
        name: 'PurchaseDocuments',
        component: () => import('@/pages/Purchases/PurchaseDocuments.vue'),
        meta: { permission: 'purchases.manage' }
      }
    ]
  },
  {
    path: '/sales/invoices',
    component: DashboardLayout,
    children: [
      {
        path: '',
        name: 'InvoicesList',
        component: () => import('@/pages/Invoices/InvoicesList.vue'),
        meta: {
          title: 'Invoices',
          permission: 'invoices.manage'
        },
      }
    ]
  },

  {
    path: '/invoices/new',
    name: 'NewInvoice',
    component: () => import('@/pages/Invoices/NewInvoice.vue'),
    meta: {
      title: 'New Invoice',
      permission: 'invoices.manage'
    },
  },

  {
    path: '/invoices/:id',
    name: 'ViewInvoice',
    component: () => import('@/pages/Invoices/ViewInvoice.vue'),
    meta: {
      title: 'View Invoice',
      permission: 'invoices.manage'
    },
  },

  {
    path: '/invoices/:id/edit',
    name: 'EditInvoice',
    component: () => import('@/pages/Invoices/NewInvoice.vue'),
    meta: {
      title: 'Edit Invoice',
      permission: 'invoices.manage'
    },
  },

  {
    path: '/sales/quotations',
    component: DashboardLayout,
    children: [
      {
        path: '',
        name: 'QuotationsList',
        component: () => import('@/pages/Invoices/QuotationsList.vue'),
        meta: {
          title: 'Quotations',
          permission: 'quotations.manage'
        },
      }
    ]
  },

  {
    path: '/quotations/new',
    name: 'NewQuotation',
    component: () => import('@/pages/Invoices/NewQuotation.vue'),
    meta: {
      title: 'New Quotation',
      permission: 'quotations.manage'
    },
  },

  {
    path: '/quotations/:id/edit',
    name: 'EditQuotation',
    component: () => import('@/pages/Invoices/NewQuotation.vue'),
    meta: {
      title: 'Edit Quotation',
      permission: 'quotations.manage'
    },
  },

  {
    path: "/expenses",
    component: DashboardLayout,
    children: [
      { path: "", redirect: "list" },

      {
        path: "list",
        component: () => import("@/pages/Expenses/ExpensesList.vue"),
        meta: { permission: "expenses.manage" }
      },

      {
        path: "categories",
        component: () => import("@/pages/Expenses/CategoriesList.vue"),
        meta: { permission: "expense_categories.manage" }
      },
    ],
  },
  // SUPPLIERS
 {
  path: "/suppliers/list",
  component: DashboardLayout,
  children: [
    {
      path: "",
      component: SuppliersList,
      meta: { permission: "suppliers.manage" }
    },
    {
      path: ":id",
      name: "ViewSupplier",
      component: ViewSupplier,
      meta: { permission: "suppliers.manage" }
    },
  ],
},

  // BRANCHES
  {
    path: "/branches/list",
    component: DashboardLayout,
    children: [
      { path: "", component: Branches, meta: { permission: "branches.view" } },
    ],
  },

  // REPORTS
  {
    path: "/reports",
    component: DashboardLayout,
    children: [
      { path: "general", component: GeneralReport, meta: { permission: "reports.view" } },
      { path: "transactions", component: Transactions, meta: { permission: "reports.view" } },
    ],
  },

  // HRM
  {
    path: "/hrm",
    component: DashboardLayout,
    children: [
      {
        path: "staff",
        component: StaffList,
        meta: { permission: "staff.manage" }
      },
      {
        path: "employees",
        component: () => import("@/pages/HRM/Employees/EmployeeList.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "employees/new",
        component: () => import("@/pages/HRM/Employees/EmployeeForm.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "employees/:id/edit",
        component: () => import("@/pages/HRM/Employees/EmployeeForm.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "employees/:id",
        component: () => import("@/pages/HRM/Employees/EmployeeProfile.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "awards",
        component: () => import("@/pages/HRM/Awards.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "warnings",
        component: () => import("@/pages/HRM/Warnings.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "resignations",
        component: () => import("@/pages/HRM/Resignations.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "terminations",
        component: () => import("@/pages/HRM/Terminations.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "performance",
        component: () => import("@/pages/HRM/PerformanceReviews.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "performance/new",
        component: () => import("@/pages/HRM/PerformanceReviewForm.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "performance/:id/edit",
        component: () => import("@/pages/HRM/PerformanceReviewForm.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "departments",
        component: () => import("@/pages/HRM/Departments.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "shifts",
        component: () => import("@/pages/HRM/Shifts.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "attendance",
        component: () => import("@/pages/HRM/Attendance.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "overtimes",
        component: () => import("@/pages/HRM/Overtimes.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "leave-types",
        component: () => import("@/pages/HRM/LeaveTypes.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "leaves",
        component: () => import("@/pages/HRM/Leaves.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "leave-balances",
        component: () => import("@/pages/HRM/LeaveBalances.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "payroll",
        component: () => import("@/pages/HRM/PayrollPeriods.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "payroll/config",
        component: () => import("@/pages/HRM/PayrollConfig.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "payroll/:id",
        component: () => import("@/pages/HRM/PayrollPeriodDetail.vue"),
        meta: { permission: "staff.manage" }
      },
      {
        path: "salary-advances",
        component: () => import("@/pages/HRM/SalaryAdvances.vue"),
        meta: { permission: "staff.manage" }
      },
    ],
  },

  // CRM
  {
    path: "/crm",
    component: DashboardLayout,
    children: [
      {
        path: "dashboard",
        component: () => import("@/pages/CRM/CrmDashboard.vue"),
        meta: { permission: "crm.dashboard" }
      },
      {
        path: "contacts",
        component: () => import("@/pages/CRM/Contacts.vue"),
        meta: { permission: "crm.contacts" }
      },
      {
        path: "contacts/:id",
        component: () => import("@/pages/CRM/ContactDetail.vue"),
        meta: { permission: "crm.contacts" }
      },
      {
        path: "my-tasks",
        component: () => import("@/pages/CRM/MyTasks.vue"),
        meta: { permission: "crm.activities" }
      },
      {
        path: "companies",
        component: () => import("@/pages/CRM/Companies.vue"),
        meta: { permission: "crm.companies" }
      },
      {
        path: "sources",
        component: () => import("@/pages/CRM/Sources.vue"),
        meta: { permission: "crm.sources" }
      },
      {
        path: "tags",
        component: () => import("@/pages/CRM/Tags.vue"),
        meta: { permission: "crm.tags" }
      },
      {
        path: "deals",
        component: () => import("@/pages/CRM/Deals.vue"),
        meta: { permission: "crm.deals" }
      },
      {
        path: "pipeline-stages",
        component: () => import("@/pages/CRM/PipelineStages.vue"),
        meta: { permission: "crm.pipeline_settings" }
      },
      {
        path: "campaigns",
        component: () => import("@/pages/CRM/Campaigns.vue"),
        meta: { permission: "crm.campaigns" }
      },
      {
        path: "loyalty",
        component: () => import("@/pages/CRM/Loyalty.vue"),
        meta: { permission: "loyalty.manage" }
      },
    ],
  },
  {
    path: "/roles",
    component: DashboardLayout,
    children: [
      { path: "list", component: RoleList, meta: { permission: "roles.manage" } },

    ],
  },
  // ECOMMERCE
  {
    path: "/ecommerce",
    component: DashboardLayout,
    children: [
      {
        path: "stores",
        name: "EcommerceStores",
        component: () => import("@/pages/Ecommerce/StoresList.vue"),
        meta: { permission: "ecommerce.manage" },
      },
      {
        path: "stores/:id",
        name: "EcommerceStoreView",
        component: () => import("@/pages/Ecommerce/StoreView.vue"),
        meta: { permission: "ecommerce.manage" },
      },
    ],
  },

  // SETTINGS
  {
    path: "/settings",
    component: DashboardLayout,
    children: [
      { path: "", component: Settings },
    ],
  },

  // SUBSCRIPTION
  {
    path: "/subscription",
    component: DashboardLayout,
    children: [{ path: "", component: SubscriptionPage }],
  },

  // SUPPORT
  {
    path: "/support",
    component: DashboardLayout,
    children: [{ path: "", component: SupportPage }],
  },

  // PROFILE
  {
    path: "/profile",
    component: DashboardLayout,
    children: [
      {
        path: "",
        name: "Profile",
        component: () => import("@/pages/Profile/Profile.vue"),
        meta: { title: "My Profile" },
      },
      {
        path: "password",
        name: "ChangePassword",
        component: () => import("@/pages/Profile/ChangePassword.vue"),
        meta: { title: "Change Password" },
      },
    ],
  },

  // 404 — must stay last so every route above gets a chance to match first
  {
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    component: () => import("@/pages/NotFound.vue"),
    meta: { public: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore();

  const staticPublic = ["/", "/login", "/register", "/privacy"];
  const isPublic = staticPublic.includes(to.path) || to.matched.some(r => r.meta?.public);

  if (!isPublic && !auth.token) return next("/login");

  if (!isPublic && auth.token) {
    try {
      await axios.get("/api/auth/verify-token", {
        headers: { Authorization: `Bearer ${auth.token}` },
      });

      const isSuperAdminRoute = to.matched.some(r => r.meta?.superAdmin);

      // Verify superadmin status via API (cached in memory for the session, never localStorage)
      const isSuperAdmin = await auth.checkSuperAdmin();

      // Super admin users must stay in /superadmin — redirect away from regular dashboard
      if (isSuperAdmin && !isSuperAdminRoute && to.path !== "/login") {
        return next("/superadmin/dashboard");
      }

      // Regular users cannot access /superadmin routes
      if (!isSuperAdmin && isSuperAdminRoute) {
        return next("/dashboard");
      }

      // Super admins bypass subscription check entirely
      if (!isSuperAdmin && to.path !== "/subscription" && to.path !== "/support") {
        const sub = auth.subscription;
        if (!sub || !sub.is_active) {
          try {
            const res = await axios.get("/api/subscription/current");
            auth.setSubscription(res.data?.data ?? null);
          } catch {
            auth.setSubscription(null);
          }
          const fresh = auth.subscription;
          if (!fresh || !fresh.is_active) {
            return next("/subscription");
          }
        }
      }

      // Permission check (not applied to superadmin routes)
      if (!isSuperAdmin && to.meta.permission && !auth.hasPermission(to.meta.permission)) {
        return next("/dashboard");
      }

      return next();
    } catch {
      auth.clearToken();
      return next("/login");
    }
  }

  next();
});

export default router;