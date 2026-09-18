#!/bin/bash
set -e

# Default PORT to 10000 if not passed by Render
export PORT=${PORT:-10000}

# Configure Apache to listen on Render's dynamic PORT
sed -i "s/Listen [0-9]*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Setup SQLite database file if not present
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Set proper ownership and permissions for Laravel
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 664 /var/www/html/database/database.sqlite

# Run Laravel migrations and idempotent seeding
php artisan migrate --force
php artisan db:seed --force || true
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
