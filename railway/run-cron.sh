#!/bin/bash
# Scheduler service. Runs the Laravel scheduler once a minute.
set -e

php artisan config:cache

while true; do
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done
