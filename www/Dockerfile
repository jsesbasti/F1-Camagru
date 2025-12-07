FROM php:8.2-cli

USER 1000:1000

COPY --chown="1000:1000" . /usr/src/web
WORKDIR /usr/src/web

CMD [ "php", "-S", "0.0.0.0:8000", "router.php"]

EXPOSE 8000