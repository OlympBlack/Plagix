#!/bin/bash
set -e

cd /var/www/html

echo "=== Vérification des variables ==="
echo "DB_CONNECTION=$DB_CONNECTION"
echo "DATABASE_URL=${DATABASE_URL:0:30}..."  # affiche seulement le début

# Vider tout cache figé au build
echo " Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Reconstruire le cache APRÈS que les vars d'env sont disponibles
echo "⚡ Optimisation Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "🗄️ Running migrations..."
    php artisan migrate --force

    echo "🌱 Seeding..."
    php artisan db:seed --class=ScrapingSourcesSeeder --force

    if [ "$START_SCRAPE_ON_BOOT" = "true" ]; then
        echo "Triggering scraping..."
        php artisan scraping:start-all
    fi
fi

echo "Démarrage: $@"
exec "$@"