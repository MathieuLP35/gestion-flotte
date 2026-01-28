#!/bin/sh
set -e

# Répertoires storage et cache
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public bootstrap/cache

# Permissions - toujours essayer de fixer les permissions
# Si on tourne en root, on peut changer le propriétaire
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
else
    # Si on tourne en www-data, on fixe juste les permissions
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
fi

# Liens et caches Laravel (ignorer les erreurs si .env incomplet)
php artisan storage:link 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

exec "$@"
