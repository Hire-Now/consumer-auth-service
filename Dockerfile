FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    zip unzip curl libzip-dev libonig-dev libpq-dev && \
    docker-php-ext-install zip pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./public /var/www/html
COPY ./ /var/www/html
WORKDIR /var/www/html

RUN a2enmod rewrite

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html

RUN composer install

EXPOSE 80
