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
- Role-based permissions (roles own a set of permissions; users own a role)

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
- Subscription plans and billing, with a `subscription` middleware gate on paid features
- DigiTax (KRA eTIMS) integration for tax-compliant invoicing, webhook-authenticated
- A super-admin area for managing tenant businesses across the platform
- An e-commerce storefront layer for businesses that sell online (`/marketplace`)

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
php artisan queue:work   # required for report exports and background jobs
```

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
- **Frontend service layer**: each backend resource has a matching `src/services/*Service.js` wrapping the shared Axios instance (`src/services/api.js`), which centralizes the base URL, auth header, and 401/402 session handling.

---

## Repository layout

```
Bizaa/
├── Backend/    Laravel API — app/, routes/api.php, database/migrations, ...
└── Frontend/   Vue 3 SPA — src/pages, src/services, src/router
```
