#!/bin/bash
set -e

# Data Directory
DB_FILE=/var/www/html/storage/app/database.sqlite

# Create database file if it doesn't exist
if [ ! -f "$DB_FILE" ]; then
    echo "Creating SQLite database..."
    touch "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
fi

# Run Migrations
echo "Running migrations..."
php artisan migrate --force

# Seed Admin User
echo "Seeding Admin User..."
php artisan db:seed --class=AdminUserSeeder --force

# Fix Permissions for Logs and Cache
echo "Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
