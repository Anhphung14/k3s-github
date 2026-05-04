# --- Stage 1: Build Frontend ---
FROM node:22.10.0-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install 

COPY . .
RUN npm run build

# --- Stage 2: Serve Application ---
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev zip unzip oniguruma-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY composer*.json ./
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --no-scripts --no-autoloader --no-dev

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize && \
    php artisan route:clear && \
    php artisan config:clear

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
