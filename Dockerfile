# --- Étape 1 : Build du Frontend (Vite/VueJS) ---
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Étape 2 : Serveur Application PHP ---
FROM php:8.4-fpm-alpine
WORKDIR /var/www

# Installation des extensions système et PHP nécessaires
RUN apk add --no-cache \
    libpng-dev libzip-dev zip unzip git icu-dev libpq-dev \
    && docker-php-ext-install pdo_pgsql bcmath zip intl

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie du code source et des assets compilés
COPY . .
COPY --from=frontend-builder /app/public/build ./public/build
# Sauvegarder public/build dans un emplacement qui ne sera pas écrasé par le volume
RUN if [ -d "public/build" ] && [ -n "$(ls -A public/build 2>/dev/null)" ]; then \
        cp -r public/build /tmp/public-build-backup && \
        echo "Backup de public/build créé dans /tmp/public-build-backup" && \
        ls -la /tmp/public-build-backup | head -3; \
    else \
        echo "ATTENTION: public/build est vide ou n'existe pas après le build Vite!"; \
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