#!/bin/sh
set -e

# Répertoires storage et cache
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public bootstrap/cache

# Permissions (si on tourne en root)
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi

# Liens et caches Laravel (ignorer les erreurs si .env incomplet)
php artisan storage:link 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

exec "$@"
