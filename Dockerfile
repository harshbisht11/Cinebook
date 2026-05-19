FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-mysqli \
    libapache2-mod-php8.1 \
    mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable rewrite
RUN a2enmod rewrite

# Configure Apache port 8080
RUN echo "Listen 8080" > /etc/apache2/ports.conf \
    && echo '<VirtualHost *:8080>\nDocumentRoot /var/www/html\nServerName localhost\n<Directory /var/www/html>\nAllowOverride All\nRequire all granted\nOptions -Indexes\n</Directory>\n</VirtualHost>' \
    > /etc/apache2/sites-enabled/000-default.conf

# Remove default Apache page BEFORE copying our files
RUN rm -f /var/www/html/index.html

# Copy our app files
COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads

COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]