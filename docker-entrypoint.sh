#!/bin/bash
set -e

HOST="${MYSQLHOST:-localhost}"
PORT="${MYSQLPORT:-3306}"
USER="${MYSQLUSER:-root}"
PASS="${MYSQLPASSWORD:-}"
DB="${MYSQLDATABASE:-movie_booking}"
SITE_PORT="${PORT:-8080}"

echo "🎬 CineBook starting on port $SITE_PORT..."
echo "🗄  Database: $USER@$HOST:$PORT/$DB"

# Wait for MySQL
echo "⏳ Waiting for MySQL..."
for i in $(seq 1 30); do
    if mysqladmin ping -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} --ssl=0 --silent 2>/dev/null; then
        echo "✅ MySQL is ready!"
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "❌ MySQL not reachable after 60s. Continuing anyway..."
    fi
    sleep 2
done

# Create DB and run schema
mysql -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} --ssl=0 -e \
    "CREATE DATABASE IF NOT EXISTS \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

TABLES=$(mysql -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} --ssl=0 "$DB" \
    -e "SHOW TABLES LIKE 'movies';" 2>/dev/null | grep -c movies || true)

if [ "$TABLES" -eq 0 ]; then
    echo "🏗  Running database schema setup..."
    mysql -h"$HOST" -P"$PORT" -u"$USER" ${PASS:+-p"$PASS"} --ssl=0 "$DB" \
        < /var/www/html/sql/database.sql
    echo "✅ Schema applied!"
else
    echo "✅ Database already set up."
fi

# Set Apache port
echo "Listen 8080" > /etc/apache2/ports.conf
sed -i "s|<VirtualHost \*:[0-9]*>|<VirtualHost *:8080>|g" \
    /etc/apache2/sites-enabled/000-default.conf 2>/dev/null || true

echo "🚀 Starting Apache on port 8080..."
exec apache2-foreground
