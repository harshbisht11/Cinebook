FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && docker-php-ext-install mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Fix MPM issue
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
          /etc/apache2/mods-enabled/mpm_worker.load 2>/dev/null || true
RUN a2enmod mpm_prefork rewrite

# Set port 8080
RUN echo "Listen 8080" > /etc/apache2/ports.conf \
    && sed -i 's|<VirtualHost \*:80>|<VirtualHost *:8080>|g' \
       /etc/apache2/sites-enabled/000-default.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' \
       /etc/apache2/apache2.conf

# Remove default page
RUN rm -f /var/www/html/index.html

# Copy app
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/uploads \
    && chmod 775 /var/www/html/uploads

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
CMD ["/entrypoint.sh"]