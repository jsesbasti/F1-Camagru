FROM php:8.2-cli

WORKDIR /usr/src/myapp
RUN apt update && apt upgrade -y && docker-php-ext-install mysqli pdo pdo_mysql

COPY ./php.ini-development /usr/local/etc/php/php.ini
COPY . .

CMD [ "php", "-S", "0.0.0.0:3000"]

#Delete this for security
EXPOSE 3000