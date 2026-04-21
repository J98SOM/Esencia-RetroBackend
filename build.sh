#!/bin/bash
set -e

# Install PHP dependencies
composer install --no-dev --no-interaction

# Run migrations with seed
php artisan migrate --seed --force

# Optimize for production
php artisan optimize

echo "✓ Migrations and seeding completed successfully!"
