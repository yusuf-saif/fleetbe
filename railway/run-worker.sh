#!/bin/bash
# Queue worker service.
# The mailables in app/Mail implement ShouldQueue, so a worker is required
# for password-reset / registration emails to be delivered.
set -e

php artisan config:cache

# --max-time recycles the process periodically to pick up code/env changes.
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --timeout=90
