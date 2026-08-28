#!/usr/bin/env bash
# Run this after every `git pull` (or however new code lands) on the
# production server. Every step here is idempotent — safe to run even
# when nothing relevant to that step actually changed — so this is
# "always run the whole thing," not "figure out which steps you need."
#
# Usage:
#   cd /var/www/html/Bizaa/Backend
#   bash deploy/deploy.sh

set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> composer install"
composer install --no-dev --optimize-autoloader

echo "==> database migrations"
php artisan migrate --force

echo "==> permissions/roles sync (new Permission rows + re-sync onto every
     business's Admin role — see RolesAndPermissionsSeeder for what this
     actually does and why it's always safe to re-run)"
php artisan db:seed --class=RolesAndPermissionsSeeder --force

echo "==> clear + rebuild caches (route/config/view) so nothing served is
     stale from before this deploy"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

echo "==> restart php-fpm (drops any opcache-cached bytecode of changed files)"
sudo systemctl restart php8.3-fpm

echo "==> restart the queue worker(s) so they pick up new/changed job code
     (Supervisor respawns them — see deploy/QUEUE_WORKER.md if this errors,
     it means the worker isn't set up as a Supervisor program yet)"
php artisan queue:restart

echo "==> done"
