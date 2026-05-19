FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && docker-php-ext-install mysqli \
    && a2enmod rewrite \
    && a2dismod mpm_event || true \
    && a2enmod mpm_prefork \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads

COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]