# ─── CineBook — Railway Dockerfile ───────────────────────────────────────────
FROM php:8.2-apache

# Install MySQLi and required tools
# Explicitly disable conflicting MPMs and enable only prefork (required for php)
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && docker-php-ext-install mysqli \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Allow .htaccess overrides
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copy app into web root
COPY . /var/www/html/

# Ensure uploads directory is writable
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads

# Copy and enable entrypoint
COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# NOTE: ports.conf is written at runtime by entrypoint.sh — do NOT write it here
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]