FROM php:8.3-cli as vendor

RUN apt-get update && apt-get install -y git unzip

WORKDIR /app

COPY composer.json composer.lock ./
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --no-scripts --no-progress --prefer-dist

FROM dunglas/frankenphp:latest-alpine

WORKDIR /app

RUN apk add --no-cache mysql-client

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN php artisan optimize && \
    chmod -R 777 storage bootstrap/cache

EXPOSE 8000

RUN echo '#!/bin/sh' > /entrypoint.sh && \
    echo 'php artisan migrate --seed --force' >> /entrypoint.sh && \
    echo 'exec frankenphp run --addr 0.0.0.0:8000' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
