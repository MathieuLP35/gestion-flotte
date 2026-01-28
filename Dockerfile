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

# Installation des dépendances PHP sans les outils de dev
RUN composer install --no-dev --optimize-autoloader

# Permissions pour Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]