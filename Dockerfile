# Dockerfile
FROM php:7.1-apache

RUN docker-php-ext-install pdo_mysql
RUN a2enmod rewrite

ADD . /var/www
ADD ./public /var/www/html

RUN mkdir -p /var/www/storage/logs
RUN mkdir -p /var/www/storage/app
RUN mkdir -p /var/www/storage/framework/cache
RUN mkdir -p /var/www/storage/framework/sessions
RUN mkdir -p /var/www/storage/framework/testing
RUN mkdir -p /var/www/storage/framework/views
RUN chown -R www-data:www-data /var/www
