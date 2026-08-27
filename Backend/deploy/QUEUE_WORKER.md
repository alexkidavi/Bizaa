# Queue worker

`QUEUE_CONNECTION=database` (see `.env`) — jobs land in the `jobs` table and
sit there until something runs `php artisan queue:work` against them. In
development that "something" is whoever remembers to run it by hand; in
production it must be a supervised, always-restarting process, or these
features silently do nothing:

- **CRM campaign sends** — `CrmCampaignService::send()` dispatches a
  `Bus::batch()` of `App\Jobs\SendCrmCampaignRecipientJob` (one per
  recipient) plus a `finally()` callback that settles the campaign's final
  status. Without a worker, a campaign gets stuck on `status: sending`
  forever and no SMS/WhatsApp/email actually goes out.
- **Report exports** — `ReportExportController::store()` dispatches
  `App\Jobs\GenerateReportExportJob`. Without a worker, the export sits at
  `status: pending` forever and the frontend's poll/download never
  resolves.

## Production setup

Use `deploy/supervisor-bizaa-worker.conf` (Supervisor) — copy it to
`/etc/supervisor/conf.d/`, fix the paths/user for your host, then:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start bizaa-worker:*
```

Two worker processes (`numprocs=2`) is enough at current scale — bump it
if campaign sends or exports start queueing up faster than they drain
(check with `php artisan queue:monitor database:default --max=100` or by
watching `SELECT COUNT(*) FROM jobs`).

**After every deploy**, restart workers so they pick up the new code —
Supervisor keeps the *process* alive, but a running PHP worker keeps
executing whatever code it loaded when it started:

```bash
php artisan queue:restart
```

Add that to the deploy script, right after `php artisan migrate --force`.

## Local development

Run a worker by hand whenever you need to test something that dispatches
a job (campaign send, report export):

```bash
php artisan queue:work --queue=default --tries=3
```

Leave it running in its own terminal. Failed jobs (after retries are
exhausted) land in `failed_jobs` — inspect with
`php artisan queue:failed`, retry with `php artisan queue:retry all`.

## If a real queue driver is ever needed

`database` is fine at this scale (single-digit jobs/sec). If campaign
volume grows enough that DB polling becomes the bottleneck, switch
`QUEUE_CONNECTION` to `redis` (needs `predis/predis` or the `phpredis`
extension) — no job/service code changes required, it's purely a
connection swap in `.env` + `config/queue.php`.
