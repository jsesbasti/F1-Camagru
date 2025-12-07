FROM php:8.5-fpm-alpine

WORKDIR /app/camagru
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . .