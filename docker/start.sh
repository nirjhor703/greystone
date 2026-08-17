#!/bin/bash

set -e

cd /var/www/html

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

php-fpm -D

nginx -g "daemon off;"