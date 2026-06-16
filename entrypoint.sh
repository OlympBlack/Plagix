#!/bin/sh
set -e

# Run standard Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Execute the CMD from Dockerfile
exec "$@"
