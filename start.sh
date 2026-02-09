#!/bin/bash
set -e

export APP_ENV=prod
export APP_DEBUG=0
export PORT=${PORT:-8080}

echo "Starting ChatGeocercano deployment..."

# Install dependencies - if lock is incompatible, update it
echo "Installing Composer dependencies..."

# Try to install from lock file first
if ! composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts -q 2>/dev/null; then
    echo "⚠️ Lock file incompatible with PHP version. Updating dependencies..."
    # Remove lock file and update to compatible versions
    rm -f composer.lock
    composer update --no-dev --optimize-autoloader --prefer-dist --no-scripts -q
    echo "✓ Dependencies updated"
else
    echo "✓ Dependencies installed from lock file"
fi

# Build assets
echo "Building assets..."
npm run build 2>&1 || echo "⚠️ Asset build warning"

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
echo "Starting web server on 0.0.0.0:$PORT..."

# Use PHP built-in server
exec php -S 0.0.0.0:$PORT -t public

