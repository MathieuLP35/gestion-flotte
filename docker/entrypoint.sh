#!/bin/sh
# Ne pas utiliser set -e car on veut gérer les erreurs manuellement
# set -e

# Répertoires storage et cache
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public bootstrap/cache

# S'assurer que public/build existe avec les assets
# Le problème : le volume monté .:/var/www écrase le public/build de l'image
# Solution : si public/build est vide dans le volume monté, on le copie depuis le backup de l'image
echo "Vérification de public/build..."
if [ ! -d "public/build" ] || [ -z "$(ls -A public/build 2>/dev/null)" ]; then
    echo "[ENTRYPOINT] public/build est vide dans le volume monté, copie depuis l'image..."
    # Créer le répertoire
    mkdir -p public/build
    # Vérifier si le backup existe
    if [ -d "/tmp/public-build-backup" ]; then
        echo "[ENTRYPOINT] Backup trouvé dans /tmp/public-build-backup"
        if [ -n "$(ls -A /tmp/public-build-backup 2>/dev/null)" ]; then
            echo "[ENTRYPOINT] Copie de public/build depuis le backup de l'image..."
            cp -r /tmp/public-build-backup/* public/build/ 2>&1
            if [ $? -eq 0 ]; then
                echo "[ENTRYPOINT] Assets copiés avec succès!"
                ls -la public/build/ | head -5
            else
                echo "[ENTRYPOINT] Erreur lors de la copie"
            fi
        else
            echo "[ENTRYPOINT] Le répertoire backup existe mais est vide"
        fi
    else
        echo "[ENTRYPOINT] ATTENTION: Aucun backup trouvé dans /tmp/public-build-backup"
        echo "[ENTRYPOINT] Le Dockerfile doit créer le backup avec: RUN cp -r public/build /tmp/public-build-backup"
    fi
else
    echo "[ENTRYPOINT] public/build existe déjà et contient des fichiers"
    ls -la public/build/ | head -5
fi

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
        # Afficher les variables pour debug
        if [ $attempt -eq 0 ]; then
            echo "Variables DB: HOST=${DB_HOST:-non défini}, DATABASE=${DB_DATABASE:-non défini}, USER=${DB_USERNAME:-non défini}"
        fi
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
