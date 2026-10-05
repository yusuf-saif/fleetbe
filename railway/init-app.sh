#!/bin/bash
# Runs on every deploy of the app service (pre-deploy command).
set -e

# Wait for the database to accept connections. db:show only tests
# connectivity, so it succeeds against an empty database and retrying it
# cannot leave a half-applied migration behind the way retrying "migrate"
# itself would.
attempt=1
until php artisan db:show >/dev/null 2>&1; do
    if [ "$attempt" -ge 10 ]; then
        echo "Database unreachable after ${attempt} attempts. Aborting deploy."
        php artisan db:show || true
        exit 1
    fi
    echo "Database not ready (attempt ${attempt}), retrying in 3s..."
    attempt=$((attempt + 1))
    sleep 3
done

# Runs exactly once, so a genuine failure reports its real error.
php artisan migrate --force

php artisan optimize:clear

php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
