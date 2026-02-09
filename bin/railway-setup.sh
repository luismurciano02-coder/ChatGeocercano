#!/bin/bash

# Railway setup script
# This script is run once when the application is first deployed

set -e

echo "=== Railway Deployment Setup ==="

echo "Step 1: Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Step 2: Installing Node.js dependencies..."
npm install --production

echo "Step 3: Building assets..."
npm run build

echo "Step 4: Running database migrations..."
if [ -n "$DATABASE_URL" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction || true
else
    echo "Warning: DATABASE_URL not set, skipping migrations"
fi

echo "Step 5: Clearing production cache..."
php bin/console cache:clear --env=prod --no-warmup

echo "Step 6: Warming up cache..."
php bin/console cache:warmup --env=prod

echo "=== Setup Complete ==="
echo "Your application is ready to serve traffic on port ${PORT:-8000}"
