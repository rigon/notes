FROM php:apache

# Enable ReWrite module
RUN a2enmod rewrite

COPY . /var/www/html/
