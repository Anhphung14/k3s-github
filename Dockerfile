# ===== Stage 1: Composer =====
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# ===== Stage 2: Frontend =====
FROM node:22-alpine AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ===== Stage 3: Final =====
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    libpng \
    libzip \
    icu \
    oniguruma

# build extension
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip intl \
    && apk del .build-deps

WORKDIR /var/www/html

# copy minimal
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .

# cleanup Laravel
RUN rm -rf node_modules \
    && rm -rf tests \
    && rm -rf storage/logs/* \
    && php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

EXPOSE 9000
CMD ["php-fpm"]
