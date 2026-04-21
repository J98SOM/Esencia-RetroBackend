#!/bin/bash
set -e

echo "🚀 Starting Esencia RetroBackend deployment..."

# Generate APP_KEY if not exists
if [ -z "$APP_KEY" ]; then
    echo "📝 Generating APP_KEY..."
    php artisan key:generate
fi

# Install composer dependencies (if not already in vendor)
if [ ! -d "vendor" ]; then
    echo "📦 Installing composer dependencies..."
    composer install --no-dev --no-interaction
fi

# Rollback all migrations to start fresh
echo "⏮️  Rolling back all migrations..."
php artisan migrate:reset --force || true

# Run database migrations fresh with seed
echo "🗄️  Running fresh migrations with seed..."
php artisan migrate:fresh --seed --force

# Optimize application
echo "⚡ Optimizing application..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment preparation complete!"
echo "🌐 Starting server on 0.0.0.0:${PORT:=8000}"

# Start the application
php artisan serve --host 0.0.0.0 --port ${PORT:8000}
