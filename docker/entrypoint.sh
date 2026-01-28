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
    # Créer le fichier de log s'il n'existe pas et le rendre accessible
    touch storage/logs/laravel.log 2>/dev/null || true
    chown www-data:www-data storage/logs/laravel.log 2>/dev/null || true
    chmod 664 storage/logs/laravel.log 2>/dev/null || true
else
    # Si on tourne en www-data, on fixe juste les permissions
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    touch storage/logs/laravel.log 2>/dev/null || true
    chmod 664 storage/logs/laravel.log 2>/dev/null || true
fi

# Attendre que la base de données soit prête et exécuter les migrations (seulement pour le service app)
if [ "$1" = "php-fpm" ] || [ -z "$1" ]; then
    # Attendre que PostgreSQL soit prêt (essayer les migrations avec retry)
    echo "Vérification de la connexion à la base de données..."
    max_attempts=30
    attempt=0
    while [ $attempt -lt $max_attempts ]; do
        if php artisan migrate:status >/dev/null 2>&1; then
            echo "Base de données prête!"
            break
        fi
        attempt=$((attempt + 1))
        if [ $attempt -lt $max_attempts ]; then
            echo "Tentative $attempt/$max_attempts - En attente de la base de données..."
            sleep 2
        fi
    done
    
    # Exécuter les migrations
    echo "Exécution des migrations..."
    php artisan migrate --force 2>/dev/null || echo "Note: Les migrations ont peut-être déjà été exécutées"
fi

# Liens et caches Laravel (ignorer les erreurs si .env incomplet)
php artisan storage:link 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

exec "$@"
