# ---------------------------------------------------------------------
# Stage 1: Build des assets frontend (Vite)
# ---------------------------------------------------------------------
FROM node:20-alpine AS frontend

WORKDIR /app

# Variables Vite (injectées au build, overridables en CI)
ARG VITE_APP_NAME=Laravel
ARG VITE_REVERB_APP_KEY=
ARG VITE_REVERB_HOST=localhost
ARG VITE_REVERB_PORT=80
ARG VITE_REVERB_SCHEME=http

ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

COPY package*.json ./
RUN npm ci

COPY . .

# .env minimal pour Vite au build
RUN echo "VITE_APP_NAME=$VITE_APP_NAME" > .env && \
    echo "VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY" >> .env && \
    echo "VITE_REVERB_HOST=$VITE_REVERB_HOST" >> .env && \
    echo "VITE_REVERB_PORT=$VITE_REVERB_PORT" >> .env && \
    echo "VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME" >> .env

RUN npm run build

# ---------------------------------------------------------------------
# Stage 2: Application PHP
# ---------------------------------------------------------------------
FROM php:8.2-fpm-bookworm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring xml bcmath ctype json fileinfo opcache zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer (sans dev)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --ignore-platform-reqs

COPY . .

# Assets buildés par Vite
COPY --from=frontend /app/public/build ./public/build

# Autoload optimisé
RUN composer dump-autoload --optimize --no-dev

# Nettoyage
RUN rm -rf node_modules .git tests .github coverage docker

# Entrypoint (doit être copié après le rm de docker/)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["php-fpm"]
