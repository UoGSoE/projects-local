FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libgmp-dev libldap2-dev netcat && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install gd pdo pdo_mysql zip gmp bcmath pcntl ldap sysvmsg \
    && a2enmod rewrite

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/start
COPY docker/ldap.conf /etc/ldap/ldap.conf

RUN chmod u+x /usr/local/bin/start

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html
#RUN php /var/www/html/artisan view:clear

CMD ["/usr/local/bin/start"]
