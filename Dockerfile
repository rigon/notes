FROM php:apache

# Enable ReWrite module
RUN a2enmod rewrite

VOLUME /var/www/html/files/

COPY . /var/www/html/

RUN chmod a-w README.md EXAMPLES.md MARKDOWN-STYLES.md && \
    echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/upload_size.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/upload_size.ini
