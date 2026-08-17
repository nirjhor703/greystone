#!/bin/bash

set -e

cd /var/www/html

echo "Starting GreyStone..."

# Clear cached configuration
php artisan optimize:clear

# Cache Laravel configuration/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure correct permissions
chown -R www-data:www-data storage bootstrap/cache

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"