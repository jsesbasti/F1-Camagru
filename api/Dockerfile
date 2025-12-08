FROM php:8.5-alpine

WORKDIR /app/camagru
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . .

CMD ["php", "-S", "0.0.0.0:9000", "index.php"]

EXPOSE 9000