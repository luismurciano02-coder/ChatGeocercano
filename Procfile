web: npm run build && composer install --no-dev --optimize-autoloader && php bin/console doctrine:migrations:migrate --no-interaction && php -S 0.0.0.0:${PORT:-8000} -t public
