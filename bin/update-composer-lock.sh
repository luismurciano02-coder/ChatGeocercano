#!/bin/bash
# Este script regenera el composer.lock para ser compatible con PHP 8.2
# Ejecuta esto localmente ANTES de hacer push a Railway

set -e

echo "🔄 Regenerando composer.lock para PHP 8.2..."

# Remove old lock file
rm -f composer.lock

# Update dependencies
composer update --no-interaction --prefer-dist

echo "✅ composer.lock regenerado"
echo "📤 Ahora ejecuta: git add composer.lock && git commit -m 'Fix: Update composer.lock for PHP 8.2 compatibility' && git push"
