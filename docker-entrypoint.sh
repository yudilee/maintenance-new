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

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
for i in $(seq 1 30); do
    if mysqladmin ping -h"${DB_HOST:-mysql}" -u"${DB_USERNAME:-sdp}" -p"${DB_PASSWORD:-password}" --silent 2>/dev/null; then
        echo "MySQL is ready."
        break
    fi
    echo "Attempt $i: MySQL not ready yet, waiting 2 seconds..."
    sleep 2
done

# Run Migrations
echo "Running migrations..."
php artisan migrate --force


# Fix Permissions for Logs and Cache
echo "Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
