#!/bin/bash
set -e

HOST="${MYSQLHOST:-localhost}"
PORT="${MYSQLPORT:-3306}"
USER="${MYSQLUSER:-root}"
PASS="${MYSQLPASSWORD:-}"
DB="${MYSQLDATABASE:-movie_booking}"

echo "🎬 CineBook starting..."
echo "🗄  Database: $USER@$HOST:$PORT/$DB"

MYSQL_OPTS="-h$HOST -P$PORT -u$USER"
if [ -n "$PASS" ]; then
    MYSQL_OPTS="$MYSQL_OPTS -p$PASS"
fi
MYSQL_OPTS="$MYSQL_OPTS --ssl-mode=DISABLED"

echo "⏳ Waiting for MySQL..."
for i in $(seq 1 30); do
    if mysqladmin ping $MYSQL_OPTS --silent 2>/dev/null; then
        echo "✅ MySQL is ready!"
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "❌ MySQL not reachable. Continuing..."
    fi
    sleep 2
done

mysql $MYSQL_OPTS -e \
    "CREATE DATABASE IF NOT EXISTS \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

TABLES=$(mysql $MYSQL_OPTS "$DB" \
    -e "SHOW TABLES LIKE 'movies';" 2>/dev/null | grep -c movies || true)

if [ "$TABLES" -eq 0 ]; then
    echo "🏗  Running database schema setup..."
    mysql $MYSQL_OPTS "$DB" < /var/www/cinebook/sql/database.sql
    echo "✅ Schema applied!"
else
    echo "✅ Database already set up."
fi

echo "🚀 Starting Apache..."
exec apache2ctl -D FOREGROUND