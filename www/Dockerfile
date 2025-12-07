FROM php:8.2-cli


COPY . /usr/src/web
WORKDIR /usr/src/web

CMD [ "php", "-S", "0.0.0.0:8000", "router.php"]

EXPOSE 8000