# Bizaa

Bizaa is a multi-tenant, multi-branch Point of Sale and business management platform for retail and service businesses. It covers the full operating loop of a business — selling, purchasing, stock, expenses, staff, and reporting — behind a single login, with each business's data isolated from every other business on the platform.

The system is split into two applications that share one API contract:

| | Path | Stack |
|---|---|---|
| **Backend** | [`Backend/`](Backend) | Laravel 12 (PHP 8.2+), MySQL, JWT auth, queued jobs |
| **Frontend** | [`Frontend/`](Frontend) | Vue 3 (`<script setup>`), Vite, Pinia, Vue Router, Tailwind |

---

## System architecture overview

```
┌─────────────────────┐        HTTPS / JSON         ┌──────────────────────────┐
│  Frontend (Vue 3)    │ ───────────────────────────▶│  Backend (Laravel 12)    │
│  SPA, Vite build     │◀─────────────────────────── │  routes/api.php only —   │
│  Pinia + Vue Router  │        JWT bearer token      │  no server-rendered      │
└─────────────────────┘                              │  views except PDF/email  │
                                                       │  templates               │
                                                       └───────────┬──────────────┘
                                                                   │
                                        ┌──────────────────────────┼──────────────────────────┐
                                        ▼                          ▼                          ▼
                                ┌───────────────┐        ┌──────────────────┐       ┌──────────────────┐
                                │  MySQL         │        │  `jobs` table     │       │  External APIs    │
                                │  (row-level    │        │  (QUEUE_CONNECTION│       │  M-Pesa, DigiTax, │
                                │  multi-tenant  │        │  =database) →     │       │  WhatsApp Cloud   │
                                │  via business_id)│      │  queue:work /     │       │  API              │
                                └───────────────┘        │  Supervisor       │       └──────────────────┘
                                                          └──────────────────┘
```

- **Frontend and backend are fully decoupled** — the Vue app is a static build talking to the Laravel app purely over its JSON API (`Frontend/src/services/api.js`); there's no shared session state, no server-rendered Blade views in the authenticated app (Blade is used only for PDF/Excel export templates and outgoing emails).
- **Single database, row-level multi-tenancy** — one MySQL instance for every business on the platform; isolation is `business_id` scoping enforced in the repository layer, not separate schemas/databases per tenant (see Database structure and Architecture notes → Tenancy).
- **Two request paths**: synchronous (most reads/writes, including the M-Pesa STK initiation and the DigiTax sale submission — both are plain outbound HTTP calls made inline) and queued (CRM campaign sends, report exports, the welcome email) — see API reference and Integration details below for which is which.
- **Two authentication realms**: normal tenant users (JWT `auth:api`, scoped to their `business_id` + role permissions) and platform super-admins (`is_superadmin` flag, separate `superadmin` middleware, `/superadmin/*` routes) — see Architecture notes.
- **Two client applications share the one API**: the authenticated SPA (`Frontend/src/pages`, everything behind login) and the public marketing/marketplace surface (landing page, `/marketplace`, Privacy page) — the latter calls a handful of intentionally unauthenticated routes (see API reference: routes marked *public*).

---

## Core features

**Sales & POS**
- Point-of-sale screen for walk-in sales, with M-Pesa STK Push and manual payment confirmation
- Invoices and quotations, with PDF generation
- Sale-level payment ledger (full / partial / pending) with a running balance

**Purchasing & Inventory**
- Purchase orders from suppliers, with the same partial-payment ledger pattern as sales
- Multi-branch stock: stock take, stock adjustment, branch-to-branch stock transfer
- Products, categories, brands, units, per-branch stock levels

**Expenses**
- Categorized business expenses with amount, amount paid, running balance, and a payment status (`paid` / `partially_paid` / `pending`)
- A payment ledger per expense (`expense_payments`) so a bill can be paid off over several installments, with a server-side guard against overpaying

**Customers & Suppliers**
- Contact records with sales/purchase history and running account balances
- Encrypted opaque IDs for customer-facing links

**People (HRM)**
- Staff records, departments, shifts, attendance, payroll
- Performance reviews, warnings, resignations, terminations, awards, leave types & balances
- Role-based permissions (roles own a set of permissions; users own a role) — new permissions are synced onto every business's Admin role automatically on deploy, so existing tenants pick up new features without manual re-seeding

**CRM**
- Contacts and companies, independent of the Sales `Customer` record until a contact actually converts
- Deal pipeline with configurable stages and a kanban board; deals can link to a quotation and a sale once won
- Activities (calls, meetings, notes) doubling as tasks — a "My Tasks" view is just activities with a due date and no completion
- Tags and lead sources for segmentation
- Bulk campaigns (SMS / WhatsApp / Email) sent as a queued batch job per recipient, with a per-recipient delivery ledger so one bad number never blocks the rest of the send
- A CRM dashboard: pipeline value by stage, conversion funnel, activity leaderboard
- Row-level ACL: by default a user only sees CRM records assigned to them; a `*.view_all` permission lifts that per-module

**Loyalty**
- Points-based loyalty program (on by default for new and existing businesses), configurable per business under Settings

**Reporting**
16 report types, each with an on-screen view and a server-side export pipeline:

Items Sale · Close Day · Sales by Customer · Sales by Branch · Items Ledger · Customer Balances · Supplier Balances · Sale Payments · Purchase Payments · General Sales · Revenue by Item · Items Purchase · Profit · Staff Sales · General Expenses · Account Statement

Exports run **on the queue**, not in the request/response cycle:
1. `POST /reports/export` enqueues a `GenerateReportExportJob` and returns immediately
2. The frontend polls `GET /reports/export/{id}` until the job completes
3. `GET /reports/export/{id}/download` streams the finished file

Excel exports use `maatwebsite/excel`; PDF exports use `barryvdh/laravel-dompdf` against a single branded template (`resources/views/exports/report-pdf.blade.php`) that pulls each business's own logo and accent color (`businesses.report_brand_color`) into the header, summary cards, and table styling.

**Platform**
- Multi-tenant: every table that holds business data is scoped by `business_id`, enforced in repositories/services and backed by policies
- Subscription plans and billing (M-Pesa STK Push, polled from the frontend until the payment webhook lands), with a `subscription` middleware gate on paid features. The plan catalog itself (price, duration, features, active/inactive) is managed from the super-admin **Plans** page and is the single source of truth the public pricing page and the in-portal Subscription page both read live — editing a plan takes effect everywhere immediately, no deploy required
- DigiTax (KRA eTIMS) integration for tax-compliant invoicing, webhook-authenticated
- A super-admin area for managing tenant businesses, subscriptions, subscription plans, marketplace stores, and platform-wide reports across the whole platform
- Platform branding and contact info (logo, support email/phone, WhatsApp, social links) lives in a `platform_settings` table, editable from the super-admin **Settings** page, and is what the public landing page and outgoing emails render — not hardcoded
- A branded welcome email sent on signup (queued, not sent in the request/response cycle)
- An e-commerce storefront layer for businesses that sell online (`/marketplace`)
- A public marketing/landing page (`Frontend/src/pages/Landing`) with SEO metadata, Open Graph image, and a pricing section wired to the live plan catalog above

---

## Getting started

### Backend

```bash
cd Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

```bash
php artisan migrate --seed
php artisan serve
php artisan queue:work   # required for report exports, campaign sends, and welcome emails
```

### Frontend

```bash
cd Frontend
npm install
cp .env.example .env   # then set VITE_APP_URL — see Environment configuration below
npm run dev
```

See **Environment configuration** below for what each variable actually does, and **Deployment** for taking this to a production server.

---

## Environment configuration

### Backend (`Backend/.env`)

Copied from `Backend/.env.example`. Everything below is read via Laravel's `env()`/`config()`, never hardcoded.

**Core Laravel**

| Variable | Purpose |
|---|---|
| `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL` | Standard Laravel app identity. `APP_DEBUG=false` and a real `APP_URL` in production. |
| `APP_KEY` | `php artisan key:generate` sets this — required, app won't boot without it. |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL in every real deployment; `.env.example` defaults to `sqlite` purely so a fresh checkout can boot with zero setup. |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | All default to `database` — no Redis/Memcached required to run this app. `QUEUE_CONNECTION=database` is load-bearing: see **Queued fan-out jobs** in Architecture notes, and `Backend/deploy/QUEUE_WORKER.md` for what silently breaks without a running worker. |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Set a real mailer (not `log`) for the welcome email to actually deliver. |

**Routing (production-only gotcha)**

| Variable | Purpose |
|---|---|
| `API_ROUTE_PREFIX` | Leave unset (defaults to `api`) on a normal deployment. Set to `API_ROUTE_PREFIX=""` **only** if the web server already strips `/api` before it reaches Laravel (e.g. an Apache `Alias /api -> Backend/public` on a shared vhost) — otherwise every `routes/api.php` route silently 404s because the prefix gets applied twice. See the comment in `bootstrap/app.php` for the full story; this exact bug took down the whole API once. |

**Payments — M-Pesa (Safaricom Daraja)**

| Variable | Purpose |
|---|---|
| `MPESA_CONSUMER_KEY`, `MPESA_CONSUMER_SECRET`, `MPESA_AUTH_URL` | OAuth credentials for the Daraja API. |
| `MPESA_SHORTCODE`, `MPESA_PASSKEY`, `MPESA_STK_URL`, `MPESA_CALLBACK_URL`, `MPESA_CALLBACK_SECRET` | STK Push (used for subscription payments and sale payment confirmation). |
| `MPESA_B2C_URL`, `MPESA_INITIATOR_NAME`, `MPESA_SECURITY_CREDENTIAL`, `MPESA_RESULT_URL`, `MPESA_TIMEOUT_URL` | B2C (business-to-customer) disbursements. |
| `MPESA_TRANSACTION_STATUS_URL`, `MPESA_CONFIRMATION_URL` | Optional — transaction status query / confirmation. |

**Tax compliance — DigiTax (KRA eTIMS)**

| Variable | Purpose |
|---|---|
| `DIGITAX_BASE_URL` | Defaults to DigiTax's production API — override only for a sandbox. |
| `DIGITAX_CALLBACK_URL`, `DIGITAX_CALLBACK_SECRET` | The eTIMS webhook URL and its auth token (appended to the callback path, since DigiTax doesn't support HMAC signing — see `config/services.php`). |

Per-business M-Pesa till/DigiTax credentials (if a business uses its own, rather than the platform's) are stored in the `businesses` table via in-app Settings, **not** in `.env` — `.env` only holds the platform-level defaults.

**CRM campaigns — messaging providers**

| Variable | Purpose |
|---|---|
| `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_API_VERSION` | Meta WhatsApp Business Cloud API. Unset = campaigns log-only dry-run instead of sending, so the pipeline works before a WhatsApp Business Account exists. |
| `SMS_PROVIDER`, `SMS_API_KEY`, `SMS_SENDER_ID` | No vendor is wired in yet — `SmsProviderInterface` resolves to a log-only stub until `SMS_PROVIDER` is set and a real implementation is bound for it. |

**Optional**

| Variable | Purpose |
|---|---|
| `POSTMARK_TOKEN`, `RESEND_KEY`, `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY`/`AWS_DEFAULT_REGION` | Alternate mail drivers, if not using SMTP. |
| `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL` | Slack notifications, if enabled. |

### Frontend (`Frontend/.env`)

| Variable | Purpose |
|---|---|
| `VITE_APP_URL` | The backend's base URL (e.g. `http://127.0.0.1:8000` locally, `https://api.yourdomain.com` in production). Every `src/services/*.js` file builds its Axios base URL from this — nothing is hardcoded. |

---

## Database structure

MySQL, 97 tables. This is a grouped overview, not a full DDL dump — the authoritative schema is always `Backend/database/migrations/` (one file per change, applied in order); read those for exact columns/types/indexes on any given table.

| Group | Tables | Notes |
|---|---|---|
| **Auth & platform** | `users`, `roles`, `permissions`, `permission_role`, `sessions`, `password_reset_tokens`, `platform_settings`, `businesses`, `subscription_plans`, `business_subscriptions`, `mpesa_stks`, `mpesa_payments` | `businesses` is the tenant root — almost every other table below carries a `business_id` FK to it. `permission_role` is the roles↔permissions pivot; a user's permissions come from their `role_id`, not a direct grant. |
| **Queue/cache infra** | `jobs`, `job_batches`, `failed_jobs`, `cache`, `cache_locks`, `migrations` | Laravel-managed, not application data — see **Operations & maintenance**. |
| **Reference/lookup** | `counties`, `industries`, `banks`, `units`, `categories`, `brands`, `category_branch`, `brand_branch`, `unit_branch` | The `*_branch` pivots scope a lookup (unit/category/brand) to specific branches rather than the whole business. |
| **Branches** | `branches`, `branch_customer`, `branch_supplier`, `product_branch`, `purchase_branch` | `product_branch` is where per-branch **stock level** actually lives — stock is never a single number on `products`. |
| **Sales & POS** | `sales`, `sale_items`, `sale_payments`, `customers`, `quotations`, `quotation_items`, `invoices`, `invoice_items`, `invoice_payments` | `sales`/`quotations`/`invoices` each follow the parent + line-items + payments-ledger shape (see Architecture notes → Money ledgers). |
| **Purchasing & inventory** | `purchases`, `purchase_items`, `purchase_payments`, `suppliers`, `products`, `stock_histories`, `stock_takes`, `stock_take_items` | `stock_histories` is the audit trail for every stock movement (sale, purchase, transfer, adjustment, stock-take correction). |
| **Expenses** | `expenses`, `expense_categories`, `expense_payments` | Same parent + payments-ledger shape as Sales/Purchases. |
| **Loyalty** | `loyalty_settings`, `loyalty_point_transactions` | One `loyalty_settings` row per business (points rate, enabled flag); `loyalty_point_transactions` is the earn/redeem ledger. |
| **CRM** | `crm_contacts`, `crm_companies`, `crm_deals`, `crm_pipeline_stages`, `crm_activities`, `crm_tags`, `crm_contact_tag`, `crm_sources`, `crm_campaigns`, `crm_campaign_recipients` | `crm_contacts` is independent of `customers` until a contact converts. `crm_campaign_recipients` is the per-recipient send ledger for bulk SMS/WhatsApp/Email. |
| **HRM — people & time** | `departments`, `shifts`, `employee_profiles`, `attendances`, `overtimes`, `leave_types`, `leaves`, `leave_balances`, `leave_balance_adjustments` | |
| **HRM — payroll** | `payroll_periods`, `payslips`, `payslip_lines`, `payroll_settings`, `payroll_salary_components`, `payroll_statutory_rates`, `payroll_tax_bands`, `salary_advances`, `salary_advance_repayments` | |
| **HRM — performance & conduct** | `performance_cycles`, `performance_ratings`, `performance_reviews`, `awards`, `award_types`, `warnings`, `warning_types`, `resignations`, `terminations`, `termination_types`, `hrm_audit_logs` | |
| **Tax compliance** | `digitax_item_classes`, `digitax_unit_codes` | KRA eTIMS reference data (item classification codes, unit-of-measure codes) that DigiTax submissions must use. |
| **Marketplace** | `ecommerce_stores` | One storefront per business that opts into `/marketplace`; products/orders reuse the Sales & Inventory tables above rather than a parallel schema. |
| **Reports** | `report_exports` | One row per queued export job — status (`pending`/`processing`/`completed`/`failed`), file path, requested-by — polled by the frontend until it flips to `completed`. |

**Conventions that hold across almost every business table:**
- `business_id` — tenant scope, indexed, enforced in the repository layer (see Architecture notes → Tenancy)
- `created_at`/`updated_at` on everything; a handful of HRM/CRM tables also carry `deleted_at` (soft deletes) — check the migration before assuming either way
- Money columns are `decimal(10,2)` and Eloquent-cast as `decimal:2`, which **serializes as a string** — cast to `(float)` before arithmetic (a recurring gotcha in this codebase)

---

## API reference

375 routes total, all under `Backend/routes/api.php`. This is a grouped index, not a full request/response reference — controller method names match the action (`index`/`show`/`store`/`update`/`destroy` plus a few verbs like `suspend`/`toggle`/`extend`), so `php artisan route:list --path=api` is the fastest way to see an exact method+path+middleware for any one endpoint.

**Auth pattern**: every route below sits behind `auth:api` (JWT) unless marked *public*. Most business routes additionally require `subscription` (an active plan) and a `permission:<name>` middleware — see `RolesAndPermissionsSeeder` for the permission catalog. Super-admin routes (`/superadmin/*`) require the separate `superadmin` middleware, gated on `users.is_superadmin` (never mass-assignable — see Architecture notes).

| Prefix | Routes | Controller(s) | What it covers |
|---|---|---|---|
| `/auth` | 9 | `LoginController`, `RegisterController` | Login, register, password reset, change password |
| `/counties`, `/industries` | 2 | `BusinessOptionsController` | *Public.* Reference data for the registration form |
| `/subscription-plans` | 1 | `SubscriptionController` | *Public.* Live plan catalog for the landing page pricing section |
| `/platform-settings` | 1 | `PlatformSettingController` | *Public.* Branding/contact info for the landing page |
| `/business` | 6 | `BusinessController` | The logged-in user's own business record + settings |
| `/branches` | 6 | `BranchController` | Branch CRUD |
| `/customers`, `/suppliers` | 8, 7 | `CustomerController`, `SupplierController` | Contact CRUD, `/customers/pos-search` (paginated, for POS at scale) |
| `/products`, `/categories`, `/brands`, `/units` | 8, 4, 4, 4 | `ProductController` + lookups | Catalog CRUD, product search |
| `/stocks` | 10 | `StockController` | Stock take, adjustment, branch-to-branch transfer, stock history |
| `/sales` | 6 | `SaleController`, `DigitaxController` | POS sale creation, sale-level payments, eTIMS submission |
| `/purchases` | 8 | `PurchaseController` | Purchase orders + payments |
| `/quotations`, `/invoices` | 5, 5 | `QuotationController`, `InvoiceController` | Quote → sale conversion, invoicing |
| `/expenses`, `/expense-categories` | 7, 4 | `ExpenseController` + lookup | Expense CRUD + payment ledger |
| `/loyalty` | 6 | `LoyaltyController`, `LoyaltySettingController` | Points balance/history, per-business settings |
| `/crm/contacts`, `/companies` | 6, 5 | `CrmContactController`, `CrmCompanyController` | Contact/company CRUD, convert-to-customer |
| `/crm/deals`, `/pipeline-stages` | 10, 4 | `CrmDealController`, `CrmPipelineStageController` | Kanban pipeline, win/loss |
| `/crm/activities` | 7 | `CrmActivityController` | Timeline entries + tasks ("My Tasks" = activities with a due date) |
| `/crm/campaigns` | 7 | `CrmCampaignController` | Bulk SMS/WhatsApp/Email send + delivery status |
| `/crm/tags`, `/sources`, `/dashboard` | 4, 4, 1 | `CrmTagController`, `CrmSourceController`, `CrmDashboardController` | Segmentation + pipeline/funnel/leaderboard stats |
| `/hrm/employees`, `/departments`, `/shifts` | 9, 5, 5 | HRM controllers | Staff records, org structure, shift assignment |
| `/hrm/attendance`, `/overtimes`, `/leaves`, `/leave-*` | 5, 5, 7, 9 | HRM controllers | Time tracking, leave requests/balances |
| `/hrm/payroll-*`, `/payslips` | 24, 2 | Payroll controllers | Salary components, statutory rates, tax bands, periods, payslip generation |
| `/hrm/performance-*` | 13 | Performance controllers | Review cycles, ratings, reviews |
| `/hrm/awards`, `/warnings`, `/resignations`, `/terminations` + `*-types` | 22 | HRM controllers | Conduct/exit records + their configurable type lookups |
| `/reports` | 19 | `ReportController`, `ReportExportController` | On-screen report data + queued Excel/PDF export |
| `/subscription` | 6 | `SubscriptionController` | Current plan, billing history, M-Pesa STK subscribe flow |
| `/mpesa` | 6 | `MpesaController` | STK push status checks, callback (*public*, signature-checked) |
| `/digitax` | 4 | `DigitaxController` | eTIMS item classes/unit codes, callback (*public*, token-in-path) |
| `/ecommerce` | 8 | `EcommerceStoreController` | Store setup for businesses selling on `/marketplace` |
| `/marketplace/*` | 3 | `MarketplaceController` | *Public.* Storefront browsing (no auth) |
| `/roles`, `/permissions`, `/staff` | 6, 1, 5 | `RoleController`, `PermissionController`, `StaffController` | Role/permission management, staff-user accounts |
| `/superadmin/businesses`, `/subscriptions`, `/plans` | 4, 4, 6 | `SuperAdmin\*` | Tenant management, subscription grants, plan catalog CRUD |
| `/superadmin/stores`, `/reports`, `/settings`, `/dashboard` | 3, 1, 2, 1 | `SuperAdmin\*` | Marketplace moderation, platform reports, branding, overview |

---

## Integration details

**M-Pesa (Safaricom Daraja)** — `app/Services/MpesaService.php`
- **STK Push**: the frontend calls an authenticated endpoint (sale payment, or `/subscription/initiate-stk`), which requests a push to the customer's phone and returns a `checkout_request_id` immediately. The frontend polls `GET /mpesa/check-status` until Safaricom's async result lands on the public `POST /mpesa/callback` route, which resolves the matching `mpesa_stks` row and, for a subscription, calls `activateSubscription()` — reads `SubscriptionPlan::find($planId)` fresh from the DB and computes `ends_at = starts_at + duration_days`, so editing a plan's price/duration in the super-admin **Plans** page changes what a subscriber is actually charged and for how long, with no code change needed.
- **B2C**: outbound disbursements use `MPESA_INITIATOR_NAME` + `MPESA_SECURITY_CREDENTIAL` and post results to `MPESA_RESULT_URL`/`MPESA_TIMEOUT_URL`.

**DigiTax (KRA eTIMS)** — `app/Services/DigitaxService.php`
- A sale is submitted to DigiTax synchronously at creation time and returns an `offline_url` immediately — this is what the printed/emailed receipt's QR code shows at first. The KRA-verified `etims_url` arrives later, asynchronously, via the public `POST /digitax/callback/{token}` route (the `{token}` path segment is `DIGITAX_CALLBACK_SECRET`, since DigiTax doesn't support HMAC request signing) and upgrades the receipt once it lands. A sale is never blocked waiting on KRA.

**Meta WhatsApp Business Cloud API** — CRM campaigns
- Bound behind `WhatsAppProviderInterface`. With `WHATSAPP_PHONE_NUMBER_ID`/`WHATSAPP_ACCESS_TOKEN` unset, campaign sends run as a log-only dry run — the whole batch/ledger/status pipeline works and is testable before a real WhatsApp Business Account exists.

**SMS** — CRM campaigns
- No vendor is integrated yet. `SmsProviderInterface` resolves to a log-only stub (`SMS_PROVIDER` unset). Wiring in a real vendor (Africa's Talking, Twilio, etc.) means adding one implementation class and binding it in a service provider — no changes to `CrmCampaignService` or the job/ledger pipeline.

---

## Deployment

Production shape: Apache/Nginx serving the Laravel `Backend/public` as the API, a static build of `Frontend/dist` served separately (own vhost or same host, different path/subdomain), MySQL, and a Supervisor-managed queue worker. There's no Docker/CI pipeline in this repo yet — deploys are a `git pull` + script on the server.

### Backend

```bash
cd /path/to/Backend
bash deploy/deploy.sh
```

[`deploy/deploy.sh`](Backend/deploy/deploy.sh) is idempotent — safe to run after every deploy regardless of what actually changed:
1. `composer update --no-dev --optimize-autoloader`
2. `php artisan migrate`
3. `php artisan db:seed --class=RolesAndPermissionsSeeder --force` — adds any new permissions and syncs them onto every existing business's Admin role, so tenants get new features without a manual step
4. `php artisan optimize:clear` then `config:cache` + `route:cache`
5. Restart `php-fpm` (drops stale opcache)
6. `php artisan queue:restart` (workers reload their code)

First deploy to a new server, before the script above will work:
- Web server document root → `Backend/public`
- `.env` configured (see **Environment configuration**) — if the web server strips `/api` before Laravel sees it (e.g. an `Alias /api`), set `API_ROUTE_PREFIX=""`
- `php artisan storage:link`
- Queue worker under Supervisor — see [`Backend/deploy/QUEUE_WORKER.md`](Backend/deploy/QUEUE_WORKER.md) and [`Backend/deploy/supervisor-bizaa-worker.conf`](Backend/deploy/supervisor-bizaa-worker.conf). Without this, campaign sends and report exports get stuck in `pending`/`sending` forever — nothing crashes, they just never finish.

### Frontend

```bash
cd /path/to/Frontend
npm install
npm run build
```

Serve the resulting `dist/` as static files. Set `VITE_APP_URL` in `Frontend/.env` to the **production** backend URL before building — it's baked into the build output at build time, not read at runtime.

**Case-sensitive imports**: this repo is developed on Windows/macOS (case-insensitive filesystems) but deploys to Linux (case-sensitive). An import whose casing doesn't match the real filename builds fine locally and fails on the server with `Could not load .../SomeFile: ENOENT`. If a build fails only on the server, check import casing first.

---

## Operations & maintenance

**Logs**
- Backend: `Backend/storage/logs/laravel.log` (`LOG_CHANNEL=stack` → single file by default). `info()`/`report()` calls throughout the service layer (e.g. M-Pesa/DigiTax failures, subscription activation) land here — this is the first place to check when a webhook-driven feature "did nothing."
- Frontend: no server-side log — errors surface in the browser console; API errors are visible in the Network tab against the failing `VITE_APP_URL` request.
- Web server: Apache/Nginx access + error logs at their usual OS paths, outside this repo.

**Queue health**
- `SELECT COUNT(*) FROM jobs` — a growing number means the worker isn't running or can't keep up.
- `php artisan queue:failed` — list jobs that exhausted retries; `php artisan queue:retry all` to requeue them, `php artisan queue:retry {id}` for one.
- `php artisan queue:monitor database:default --max=100` — check the worker is actually draining the queue.
- After any deploy, `php artisan queue:restart` (already in `deploy.sh`) — Supervisor keeps the *process* alive, but a running worker keeps executing whatever code it loaded at start, so a deploy without this restart silently runs stale job code.

**Database backups**
- No backup automation ships in this repo — set up scheduled `mysqldump` (or your host's managed-MySQL backup feature) independently of anything here. Given the money-ledger and stock-history tables, prioritize point-in-time recovery capability (binlog-based) over daily-dump-only if the host supports it.
- Restoring: standard `mysql < backup.sql`, then `php artisan migrate` to apply anything newer than the backup, then `php artisan queue:restart` + cache clear.

**Updating dependencies**
- Backend: `composer update` (matches what `deploy.sh` already runs) — check `composer.json`'s `require` block above for what's pinned; Laravel major-version bumps need their own upgrade-guide pass, not a routine update.
- Frontend: `npm outdated` to see what's behind, `npm update` for in-range bumps. Vite/Tailwind are both on major-version-4/7 lines that move fast — read the changelog before jumping a major version, not just `npm install <pkg>@latest`.

**Known operational gotchas** (all covered in more detail elsewhere in this doc — indexed here for quick lookup)
- `API_ROUTE_PREFIX` misconfiguration → every `/api/*` route 404s (see Environment configuration)
- Case-sensitive import paths → build succeeds locally, fails on the Linux server (see Deployment)
- Missing/stopped queue worker → campaign sends and report exports silently stick at `pending`/`sending` forever (see above)
- `decimal:2`-cast money fields serialize as strings → must `(float)` cast before arithmetic (see Database structure)

---

## Third-party dependencies

**Backend** (`Backend/composer.json`)

| Package | Purpose |
|---|---|
| `laravel/framework` ^12.0 | The framework itself |
| `tymon/jwt-auth` ^2.2 | JWT authentication (`auth:api` guard) |
| `barryvdh/laravel-dompdf` ^3.1 | PDF report/invoice/receipt generation |
| `maatwebsite/excel` ^3.1 | Excel report exports |
| `laravel/tinker` ^2.10 | REPL, used constantly for verification during development |

No SMS or payment-gateway SDK is a composer dependency — M-Pesa and DigiTax are both plain HTTP calls via Laravel's `Http` facade against `config/services.php`-configured endpoints, not vendor packages.

**Frontend** (`Frontend/package.json`)

| Package | Purpose |
|---|---|
| `vue` ^3.5 | Framework (`<script setup>` throughout) |
| `vite` ^7.1 / `@vitejs/plugin-vue` | Build tool |
| `@tailwindcss/vite`, `tailwindcss` ^4.1 | Styling |
| `pinia` ^3.0 | State management (`src/stores`) |
| `vue-router` ^4.6 | Routing |
| `axios` ^1.12 | HTTP client — every `src/services/*.js` wraps this |
| `chart.js` / `vue-chartjs` | Dashboard and report charts |
| `sweetalert2` | Confirm dialogs / toasts across super-admin and app pages |
| `@heroicons/vue`, `lucide-vue-next` | Icon sets |
| `qrcode` | Receipt QR codes (eTIMS `offline_url`/`etims_url`) |
| `crypto-js` | Client-side handling of encrypted opaque IDs (see Core features → Customers) |
| `vue-multiselect`, `vue-toast-notification` | Form/UI components |

---

## Architecture notes

- **Auth**: JWT via `tymon/jwt-auth`. Every authenticated route sits behind `auth:api`; most business routes additionally require the `subscription` middleware (an active plan) and a `permission:<name>` middleware tied to the user's role.
- **Tenancy**: there is no separate database per tenant — isolation is row-level via `business_id`, applied in every repository query. Policies (`app/Policies`) double-check ownership at the authorization layer.
- **Money ledgers**: Sales, Purchases, and Expenses each follow the same shape — a parent record carries `amount`/`total`, `amount_paid` (or `paid_amount`), `balance`, and a `payment_status` enum, recomputed from a child `*_payments` table every time a payment is recorded, inside a DB transaction. New "can this be paid off over time" features should follow this same pattern rather than inventing a new one.
- **Reports**: `ReportsRepository` builds each report with an explicit `selectRaw` column list against the query builder (not `select *`), so schema changes don't silently leak into reports. `ReportExportRegistry` maps each report type to the columns/labels its Excel/PDF export shows; `GenerateReportExportJob` special-cases the two reports whose shape isn't a flat row list (Close Day, Account Statement).
- **Row-level ACL**: `app/Services/Concerns/ScopesOwnRecords.php` is a shared trait (currently used by the CRM services) that restricts a list/find query to `assigned_to = user.id` unless the user holds the module's `*.view_all` permission — which `User::hasPermission()` always grants to the `admin` role. New modules that need "see only what's assigned to me" should reuse this trait rather than re-implementing the check.
- **Queued fan-out jobs**: bulk operations (CRM campaign sends, report exports) use `Bus::batch()` — one job per unit of work, a `->finally()` callback that settles the parent record's status once every job in the batch has run, and a per-item ledger table so a single failure is visible and retryable without blocking or hiding the rest of the batch.
- **Frontend service layer**: each backend resource has a matching `src/services/*Service.js` wrapping the shared Axios instance (`src/services/api.js`), which centralizes the base URL, auth header, and 401/402 session handling.
- **Case-sensitive imports**: match filename casing exactly — see **Deployment** above for why this builds fine locally and fails on the server.

---

## Repository layout

```
Bizaa/
├── Backend/    Laravel API — app/, routes/api.php, database/migrations, ...
└── Frontend/   Vue 3 SPA — src/pages, src/services, src/router
```
