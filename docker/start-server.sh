#!/bin/sh
# Default start command for the web service: php-fpm behind Caddy.
#
# This is wired up as CMD rather than ENTRYPOINT on purpose, so that the
# worker and scheduler services can override the start command on Railway
# without Caddy being started as well.
set -e

# Laravel writes logs, compiled views and cached config at runtime.
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# Automatically run database migrations on boot
php artisan migrate --force --no-interaction || true

php-fpm --daemonize

exec caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
