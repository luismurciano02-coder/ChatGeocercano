#!/bin/bash
set -e

export APP_ENV=prod
export APP_DEBUG=0

echo "Starting ChatGeocercano deployment..."

# Install dependencies without triggering post-scripts
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts 2>&1

# Build assets
echo "Building assets..."
npm run build 2>&1

# Run migrations (allow to fail gracefully)
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate \
    --no-interaction \
    --allow-no-migration \
    --env=prod \
    2>&1 || echo "⚠️ Migration warning (non-critical)"

# Clear cache
echo "Clearing production cache..."
php bin/console cache:clear \
    --env=prod \
    --no-warmup 2>&1 || echo "⚠️ Cache clear warning (non-critical)"

# Warm up cache  
echo "Warming up production cache..."
php bin/console cache:warmup \
    --env=prod 2>&1 || echo "⚠️ Cache warmup warning (non-critical)"

echo "✓ Deployment setup complete"
echo "Starting PHP-FPM on port ${PORT:-8000}..."

# Start PHP-FPM in foreground
exec php-fpm -F -d error_log=/dev/stderr

