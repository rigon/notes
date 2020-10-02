FROM php:apache

# Enable ReWrite module
RUN a2enmod rewrite

VOLUME /var/www/html/files/

COPY . /var/www/html/
