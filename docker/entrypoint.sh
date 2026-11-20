#!/usr/bin/env bash
set -e

PORTA="${PORT:-10000}"

# Ajusta a porta dinâmica injetada pela Render
sed -ri -e "s/^Listen [0-9]+/Listen ${PORTA}/" /etc/apache2/ports.conf
sed -ri -e "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORTA}>/" /etc/apache2/sites-available/000-default.conf

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ -n "${APP_KEY:-}" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec apache2-foreground
