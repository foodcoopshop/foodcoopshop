FROM php:8.1-fpm

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/bin/

RUN docker-php-ext-install mysqli pdo pdo_mysql intl