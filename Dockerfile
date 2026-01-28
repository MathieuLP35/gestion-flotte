# --- Étape 1 : Build du Frontend (Vite/VueJS) ---
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build && \
    echo "✓ Build Vite terminé" && \
    if [ -d "public/build" ] && [ -n "$(ls -A public/build 2>/dev/null)" ]; then \
        echo "✓ public/build créé avec succès" && \
        ls -la public/build/ | head -5; \
    else \
        echo "✗ ERREUR: public/build n'existe pas ou est vide après le build!" && \
        ls -la public/ 2>&1 && \
        exit 1; \
    fi

# --- Étape 2 : Serveur Application PHP ---
FROM php:8.4-fpm-alpine
WORKDIR /var/www

# Installation des extensions système et PHP nécessaires
RUN apk add --no-cache \
    libpng-dev libzip-dev zip unzip git icu-dev libpq-dev \
    && docker-php-ext-install pdo_pgsql bcmath zip intl

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie du code source (sans public/build car il sera copié depuis frontend-builder)
COPY . .

# Copier public/build depuis le stage frontend-builder ET créer le backup directement
COPY --from=frontend-builder /app/public/build ./public/build
# Créer le backup IMMÉDIATEMENT après la copie, avant toute autre opération
RUN mkdir -p /tmp && \
    if [ -d "public/build" ] && [ -n "$(ls -A public/build 2>/dev/null)" ]; then \
        cp -r public/build /tmp/public-build-backup && \
        echo "✓ Backup de public/build créé dans /tmp/public-build-backup" && \
        ls -la /tmp/public-build-backup | head -3 && \
        echo "Contenu du backup:" && \
        ls /tmp/public-build-backup/ | head -5; \
    else \
        echo "✗ ERREUR: public/build est vide ou n'existe pas!" && \
        ls -la public/ 2>&1 && \
        exit 1; \
    fi

# Installation des dépendances PHP sans les outils de dev
RUN composer install --no-dev --optimize-autoloader

# Copie de l'entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions pour Laravel (seront réappliquées par l'entrypoint si volume monté)
RUN chown -R www-data:www-data storage bootstrap/cache || true

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]