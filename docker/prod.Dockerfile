# PHP version we are targetting
ARG PHP_VERSION=7.2

# Set up php dependancies
FROM composer:1.8 as vendor

RUN mkdir -p database/seeds
RUN mkdir -p database/factories

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --ignore-platform-reqs \
    --prefer-dist

# Build JS/css assets
FROM node:latest as frontend

RUN node --version
RUN mkdir -p /app/public

COPY package.json webpack.mix.js package-lock.json /app/
RUN mkdir /app/resources
COPY resources/ /app/resources/

WORKDIR /app

RUN yarn install
RUN yarn production

# And build the app
FROM uogsoe/soe-php-apache:${PHP_VERSION}

COPY docker/start.sh /usr/local/bin/start
COPY docker/ldap.conf /etc/ldap/ldap.conf
COPY docker/custom_php.ini /usr/local/etc/php/conf.d/custom_php.ini

RUN chmod u+x /usr/local/bin/start

COPY . /var/www/html
RUN ln -sf /run/secrets/.env /var/www/html/.env
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/js/ /var/www/html/public/js/
COPY --from=frontend /app/public/css/ /var/www/html/public/css/
COPY --from=frontend /app/mix-manifest.json /var/www/html/mix-manifest.json

RUN php /var/www/html/artisan storage:link
RUN php /var/www/html/artisan view:cache
RUN php /var/www/html/artisan config:cache
RUN php /var/www/html/artisan route:cache
RUN chown -R www-data:www-data /var/www/html

CMD ["/usr/local/bin/start"]
