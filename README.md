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
