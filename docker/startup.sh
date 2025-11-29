#!/bin/sh

# Exit on error
set -e

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate APP_KEY if not set
if grep -q "APP_KEY=" /var/www/html/.env && [ -z "$(grep "APP_KEY=" /var/www/html/.env | cut -d '=' -f 2)" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate
fi

# Cache config and routes for performance
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions again just in case
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Start Apache in foreground
echo "Starting Apache..."
exec apache2-foreground
