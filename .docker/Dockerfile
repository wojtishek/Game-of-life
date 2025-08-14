FROM php:8.4-apache

COPY --from=composer/composer:latest-bin /composer /usr/local/bin/composer
COPY --chown=www-data:www-data . /var/www/html/

RUN apt-get update && apt-get install -y libzip-dev unzip git

WORKDIR /var/www/html

EXPOSE 80