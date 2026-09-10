FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libonig-dev msmtp\
    && docker-php-ext-install pdo pdo_mysql zip gd\
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY msmtp.ini /usr/local/etc/php/conf.d/msmtp.ini

WORKDIR /var/www/html
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

# app/public/config/vendor are bind-mounted from a Kerberos-secured NFS4 share
# (sgoinfre); its ACLs only recognize the host user's identity, so the pool's
# default unprivileged "www-data" worker can't read anything under it. Run the
# pool as root instead (fpm just logs a warning about it).
RUN sed -i \
    -e 's/^user = www-data/user = root/' \
    -e 's/^group = www-data/group = root/' \
    /usr/local/etc/php-fpm.d/www.conf

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]