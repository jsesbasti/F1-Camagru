FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libonig-dev sendmail\
    && docker-php-ext-install pdo pdo_mysql zip gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html