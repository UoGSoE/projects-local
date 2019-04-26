# PHP version we are targetting
ARG PHP_VERSION=7.2

# Build JS/css assets
FROM node:10 as frontend

RUN mkdir -p /app/public /app/resources

COPY package.json webpack.mix.js package-lock.json /app/
COPY resources/ /app/resources/

WORKDIR /app

RUN npm install
RUN npm run production

# And build the app
FROM uogsoe/soe-php-apache:7.2 as prod

ENV APP_ENV=production
ENV APP_DEBUG=0

COPY docker/start.sh /usr/local/bin/start
COPY docker/app-healthcheck /usr/local/bin/app-healthcheck
COPY docker/ldap.conf /etc/ldap/ldap.conf
COPY docker/custom_php.ini* /usr/local/etc/php/conf.d/
COPY --chown=www-data:www-data . /var/www/html
COPY --from=frontend --chown=www-data:www-data /app/public/js/ /var/www/html/public/js/
COPY --from=frontend --chown=www-data:www-data /app/public/css/ /var/www/html/public/css/
COPY --from=frontend --chown=www-data:www-data /app/mix-manifest.json /var/www/html/mix-manifest.json

RUN chmod u+x /usr/local/bin/start /usr/local/bin/app-healthcheck && \
    ln -sf /run/secrets/.env /var/www/html/.env && \
    ls /usr/local/bin && \
    /usr/local/bin/composer install \
    --no-interaction \
    --no-plugins \
    --no-dev \
    --prefer-dist && \
    rm -fr /var/www/html/bootstrap/cache/*.php && \
    php /var/www/html/artisan storage:link && \
    php /var/www/html/artisan view:cache && \
    php /var/www/html/artisan route:cache

CMD ["/usr/local/bin/start"]

FROM prod as ci
ENV APP_ENV=local
ENV APP_DEBUG=1
RUN composer install \
    --no-interaction \
    --no-plugins \
    --prefer-dist
