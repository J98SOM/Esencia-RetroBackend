web: php artisan serve --host 0.0.0.0 --port ${PORT:8000}
release: composer install --no-dev && php artisan migrate:reset --force || true && php artisan migrate:fresh --seed --force
