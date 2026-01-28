#!/bin/sh
# Ne pas utiliser set -e car on veut gérer les erreurs manuellement
# set -e

# Répertoires storage et cache
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public bootstrap/cache

# Permissions - toujours essayer de fixer les permissions
# Si on tourne en root, on peut changer le propriétaire
if [ "$(id -u)" = "0" ]; then
    # Forcer le changement de propriétaire pour tous les fichiers storage (même existants)
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    # S'assurer que le répertoire logs lui-même a les bonnes permissions
    chown www-data:www-data storage/logs 2>/dev/null || true
    chmod 775 storage/logs 2>/dev/null || true
    # S'assurer que le fichier de log existe et a les bonnes permissions
    # Si le fichier existe déjà, on le supprime pour le recréer avec les bonnes permissions
    if [ -f storage/logs/laravel.log ]; then
        rm -f storage/logs/laravel.log 2>/dev/null || true
    fi
    touch storage/logs/laravel.log 2>/dev/null || true
    # Forcer le changement de propriétaire (doit fonctionner car on vient de créer le fichier)
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
    # Attendre que PostgreSQL soit prêt
    echo "Vérification de la connexion à la base de données..."
    max_attempts=30
    attempt=0
    db_ready=0
    
    while [ $attempt -lt $max_attempts ]; do
        # Tester la connexion avec une commande simple
        if php -r "try { \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT') ?: '5432').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'OK'; } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
            echo "Base de données prête!"
            db_ready=1
            break
        fi
        attempt=$((attempt + 1))
        if [ $attempt -lt $max_attempts ]; then
            echo "Tentative $attempt/$max_attempts - En attente de la base de données..."
            sleep 2
        fi
    done
    
    if [ $db_ready -eq 1 ]; then
        # Exécuter les migrations (afficher les erreurs pour debug)
        echo "Exécution des migrations..."
        if php artisan migrate --force; then
            echo "Migrations exécutées avec succès!"
        else
            echo "ERREUR: Les migrations ont échoué. Vérifiez les logs ci-dessus."
            echo "Vous pouvez exécuter manuellement: docker compose exec app php artisan migrate --force"
        fi
    else
        echo "ATTENTION: Impossible de se connecter à la base de données après $max_attempts tentatives"
        echo "Vérifiez que DB_HOST, DB_DATABASE, DB_USERNAME et DB_PASSWORD sont corrects dans .env"
    fi
fi

# Liens et caches Laravel (ignorer les erreurs si .env incomplet)
# Note: Les caches sont créés APRÈS les migrations pour éviter les problèmes
php artisan storage:link 2>/dev/null || true
# Nettoyer les caches avant de les recréer
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
# Recréer les caches
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

exec "$@"
