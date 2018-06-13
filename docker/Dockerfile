FROM uogsoe/soe-php-apache:7.2

COPY docker/start.sh /usr/local/bin/start
COPY docker/ldap.conf /etc/ldap/ldap.conf

RUN chmod u+x /usr/local/bin/start

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html
#RUN php /var/www/html/artisan view:clear

CMD ["/usr/local/bin/start"]
