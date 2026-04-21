#!/bin/bash
set -e

echo "📦 Building Esencia RetroBackend..."

# Install PHP dependencies
composer install --no-dev --no-interaction

# Rollback all migrations
echo "⏮️  Rolling back any previous migrations..."
php artisan migrate:reset --force || true

# Run fresh migrations with seed
echo "🗄️  Running fresh migrations with seed..."
php artisan migrate:fresh --seed --force

# Optimize for production
php artisan optimize

echo "✅ Build completed successfully!"
