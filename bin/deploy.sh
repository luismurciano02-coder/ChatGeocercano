#!/bin/bash

# Railway deployment script
set -e

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing Node dependencies..."
npm install
npm run build

echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Clearing cache..."
php bin/console cache:clear --env=prod

echo "Deployment completed successfully!"
