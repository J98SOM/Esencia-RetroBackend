release: php artisan migrate:reset --force --no-interaction || true && php artisan migrate:fresh --seed --force --no-interaction
web: php artisan serve --host 0.0.0.0 --port ${PORT:8000}
