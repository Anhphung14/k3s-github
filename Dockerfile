# ===== Stage 1: Composer =====
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

# ===== Stage 2: Frontend =====
FROM node:22-alpine AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ===== Stage 3: App =====
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring zip intl

WORKDIR /var/www/html

# copy dependencies
COPY --from=vendor /app/vendor ./vendor

# copy source
COPY . .

# copy frontend build
COPY --from=frontend /app/public/build ./public/build

# Laravel optimize
RUN php artisan config:clear || true && \
    php artisan route:clear || true && \
    php artisan view:clear || true && \
    php artisan event:clear || true

# permissions
RUN chmod -R 777 storage || true

EXPOSE 9000
CMD ["php-fpm"]
