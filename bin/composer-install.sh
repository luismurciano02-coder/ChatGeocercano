#!/bin/bash

# Skip all Composer scripts to avoid cache:clear errors during build
export COMPOSER_DISABLE_NETWORK=0

# Ensure production environment
export APP_ENV=prod
export APP_DEBUG=0

# Install dependencies without post-scripts
echo "Installing Composer dependencies without scripts..."
composer install --no-dev --no-scripts --optimize-autoloader --prefer-dist

echo "Dependencies installed successfully"
