#!/bin/sh
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi

# storage/ may be an empty volume on first start - recreate the layout Laravel expects
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs

if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

# Secret files (e.g. Render's /etc/secrets) may be readable only by root, but Apache
# runs as www-data - copy the DB CA certificate somewhere it can read
if [ -n "$MYSQL_ATTR_SSL_CA" ] && [ -f "$MYSQL_ATTR_SSL_CA" ]; then
    cp "$MYSQL_ATTR_SSL_CA" /usr/local/share/db-ca.pem
    chmod 644 /usr/local/share/db-ca.pem
    export MYSQL_ATTR_SSL_CA=/usr/local/share/db-ca.pem
fi

php artisan migrate --force

php artisan storage:link 2>/dev/null || true
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
