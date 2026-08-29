# Bizaa

Bizaa is a multi-tenant, multi-branch Point of Sale and business management platform for retail and service businesses. It covers the full operating loop of a business — selling, purchasing, stock, expenses, staff, and reporting — behind a single login, with each business's data isolated from every other business on the platform.

The system is split into two applications that share one API contract:

| | Path | Stack |
|---|---|---|
| **Backend** | [`Backend/`](Backend) | Laravel 12 (PHP 8.2+), MySQL, JWT auth, queued jobs |
| **Frontend** | [`Frontend/`](Frontend) | Vue 3 (`<script setup>`), Vite, Pinia, Vue Router, Tailwind |

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

Configure `.env`: database connection, `QUEUE_CONNECTION=database`, and (optional) M-Pesa / DigiTax credentials per business — those are stored per-tenant in the `businesses` table via Settings, not in `.env`.

```bash
php artisan migrate --seed
php artisan serve
php artisan queue:work   # required for report exports, campaign sends, and welcome emails
```

In production, `php artisan queue:work` runs under Supervisor rather than as a foreground process — see [`Backend/deploy/QUEUE_WORKER.md`](Backend/deploy/QUEUE_WORKER.md) for the config, and [`Backend/deploy/deploy.sh`](Backend/deploy/deploy.sh) for the full idempotent deploy sequence (composer, migrate, reseed roles/permissions, cache, `queue:restart`).

### Frontend

```bash
cd Frontend
npm install
npm run dev
```

Set `VITE_APP_URL` in `Frontend/.env` to the backend URL (e.g. `http://127.0.0.1:8000`) before starting the dev server.

---

## Architecture notes

- **Auth**: JWT via `tymon/jwt-auth`. Every authenticated route sits behind `auth:api`; most business routes additionally require the `subscription` middleware (an active plan) and a `permission:<name>` middleware tied to the user's role.
- **Tenancy**: there is no separate database per tenant — isolation is row-level via `business_id`, applied in every repository query. Policies (`app/Policies`) double-check ownership at the authorization layer.
- **Money ledgers**: Sales, Purchases, and Expenses each follow the same shape — a parent record carries `amount`/`total`, `amount_paid` (or `paid_amount`), `balance`, and a `payment_status` enum, recomputed from a child `*_payments` table every time a payment is recorded, inside a DB transaction. New "can this be paid off over time" features should follow this same pattern rather than inventing a new one.
- **Reports**: `ReportsRepository` builds each report with an explicit `selectRaw` column list against the query builder (not `select *`), so schema changes don't silently leak into reports. `ReportExportRegistry` maps each report type to the columns/labels its Excel/PDF export shows; `GenerateReportExportJob` special-cases the two reports whose shape isn't a flat row list (Close Day, Account Statement).
- **Row-level ACL**: `app/Services/Concerns/ScopesOwnRecords.php` is a shared trait (currently used by the CRM services) that restricts a list/find query to `assigned_to = user.id` unless the user holds the module's `*.view_all` permission — which `User::hasPermission()` always grants to the `admin` role. New modules that need "see only what's assigned to me" should reuse this trait rather than re-implementing the check.
- **Queued fan-out jobs**: bulk operations (CRM campaign sends, report exports) use `Bus::batch()` — one job per unit of work, a `->finally()` callback that settles the parent record's status once every job in the batch has run, and a per-item ledger table so a single failure is visible and retryable without blocking or hiding the rest of the batch.
- **Frontend service layer**: each backend resource has a matching `src/services/*Service.js` wrapping the shared Axios instance (`src/services/api.js`), which centralizes the base URL, auth header, and 401/402 session handling.
- **Case-sensitive imports**: this repo deploys to a Linux server; local development on Windows/macOS won't catch an import whose casing doesn't match the actual filename (e.g. `@/services/superAdminService` vs. the real `SuperAdminService.js`) because those filesystems are case-insensitive. It'll build locally and fail on the server. Match casing exactly.

---

## Repository layout

```
Bizaa/
├── Backend/    Laravel API — app/, routes/api.php, database/migrations, ...
└── Frontend/   Vue 3 SPA — src/pages, src/services, src/router
```
