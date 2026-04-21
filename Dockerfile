FROM php:8.3-cli-alpine

# Install required extensions and tools
RUN apk add --no-cache \
    composer \
    mysql-client \
    $PHPIZE_DEPS && \
    docker-php-ext-install \
    pdo_mysql \
    session \
    fileinfo \
    tokenizer \
    dom \
    ctype && \
    apk del $PHPIZE_DEPS

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --no-interaction --prefer-dist

# Copy entire project
COPY . .

# Set permissions
RUN chmod -R 777 storage bootstrap/cache && \
    php artisan optimize

EXPOSE 8000

# Run migrations and start server
CMD ["sh", "-c", "php artisan migrate:reset --force || true && php artisan migrate:fresh --seed --force && php -S 0.0.0.0:8000 -t public"]

