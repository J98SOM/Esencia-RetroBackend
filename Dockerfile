FROM dunglas/frankenphp:latest-alpine

RUN apk add --no-cache composer mysql-client

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction --prefer-dist

COPY . .

RUN php artisan optimize && \
    chmod -R 777 storage bootstrap/cache

EXPOSE 8000

CMD ["sh", "-c", "php artisan migrate:reset --force || true && php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=8000"]

