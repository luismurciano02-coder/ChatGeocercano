#!/bin/bash
set -e

echo "Starting ChatGeocercano deployment..."

# Install dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Build assets
echo "Building assets..."
npm run build

# Run migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Clear and warm cache
echo "Clearing cache..."
php bin/console cache:clear --env=prod

echo "Warming up cache..."
php bin/console cache:warmup --env=prod

echo "Web application is starting on port ${PORT:-8000}"

# Use PHP-FPM in production
exec php-fpm -F
