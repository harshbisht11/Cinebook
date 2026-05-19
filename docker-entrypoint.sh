#!/bin/bash
HOST="${MYSQLHOST:-localhost}"
PORT="${MYSQLPORT:-3306}"
USER="${MYSQLUSER:-root}"
PASS="${MYSQLPASSWORD:-}"
DB="${MYSQLDATABASE:-movie_booking}"

echo "🎬 CineBook starting..."
echo "🗄  Database: $USER@$HOST:$PORT/$DB"

MYSQL_OPTS="-h$HOST -P$PORT -u$USER"
[ -n "$PASS" ] && MYSQL_OPTS="$MYSQL_OPTS -p$PASS"
MYSQL_OPTS="$MYSQL_OPTS --ssl-mode=DISABLED"

echo "⏳ Waiting for MySQL..."
for i in $(seq 1 30); do
    if mysqladmin ping $MYSQL_OPTS --silent 2>/dev/null; then
        echo "✅ MySQL ready!"
        break
    fi
    [ "$i" -eq 30 ] && echo "⚠️ MySQL timeout, continuing..."
    sleep 2
done

mysql $MYSQL_OPTS -e "CREATE DATABASE IF NOT EXISTS \`$DB\`;" 2>/dev/null || true

TABLES=$(mysql $MYSQL_OPTS "$DB" -e "SHOW TABLES LIKE 'movies';" 2>/dev/null | grep -c movies || echo "0")

if [ "$TABLES" -eq "0" ]; then
    echo "🏗  Setting up database..."
    mysql $MYSQL_OPTS "$DB" < /var/www/html/sql/database.sql 2>/dev/null && echo "✅ Done!" || echo "⚠️ Schema warning"
else
    echo "✅ Database ready."
fi

echo "🚀 Starting Apache on port 8080..."
exec apache2-foreground