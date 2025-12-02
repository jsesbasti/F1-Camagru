FROM php:8.2-cli

COPY . /usr/src/myapp
WORKDIR /usr/src/myapp

CMD [ "php", "-S", "0.0.0.0:3000"]

#Delete this for security
EXPOSE 3000 