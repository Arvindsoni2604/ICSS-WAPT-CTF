FROM php:8.3-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod headers
COPY public/ /var/www/html/
COPY src/ /var/www/src/
RUN chown -R www-data:www-data /var/www/html /var/www/src
RUN printf '%s\n' 'ServerTokens Prod' 'ServerSignature Off' > /etc/apache2/conf-available/security-hardening.conf \
    && a2enconf security-hardening
EXPOSE 80
