FROM php:apache

# Enable ReWrite module
RUN a2enmod rewrite

# Install zip
RUN apt update && \
    apt install -y zip && \
    apt clean && rm -rf /var/lib/apt/lists/*

VOLUME /var/www/html/files/

COPY . /var/www/html/

RUN ln -svf ../../README.md files/readme/readme.md && \
    ln -svf ../../EXAMPLES.md files/examples/examples.md && \
    ln -svf ../../MARKDOWN-STYLES.md files/markdown_styles/markdown_styles.md && \
    chmod a-w README.md EXAMPLES.md MARKDOWN-STYLES.md && \
    chown www-data files/ && \
    echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/upload_size.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/upload_size.ini
