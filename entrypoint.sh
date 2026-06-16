#!/bin/bash
set -e

# Run standard Laravel optimizations for production
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Execute migrations and seeding if requested
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "🗄️ Running migrations..."
    php artisan migrate --force
    
    echo "🌱 Seeding initial data..."
    php artisan db:seed --class=ScrapingSourcesSeeder --force
    
    if [ "$START_SCRAPE_ON_BOOT" = "true" ]; then
        echo "📡 Triggering initial scraping..."
        php artisan scraping:start-all
    fi
fi

# Execute the CMD from Dockerfile
echo "🚀 Starting process: $@"
exec "$@"
