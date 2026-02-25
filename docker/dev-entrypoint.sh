#!/bin/sh
set -e

echo "=========================================="
echo "🔧 KeywordAI DEV Setup"
echo "=========================================="

# Ensure storage directories exist with correct permissions
echo "📁 Setting up directories..."
mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/storage/app/public \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

# Skip composer install in Docker (run on host to avoid volume corruption on macOS)
# Run: composer install --ignore-platform-reqs
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "⚠️  WARNING: vendor/autoload.php not found"
    echo "    Run: composer install --ignore-platform-reqs"
fi

# Ensure database file exists and is writable
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "📦 Creating SQLite database..."
    touch /var/www/html/database/database.sqlite
fi
chown www-data:www-data /var/www/html/database
chown www-data:www-data /var/www/html/database/database.sqlite 2>/dev/null || true
chmod 777 /var/www/html/database
chmod 666 /var/www/html/database/database.sqlite 2>/dev/null || true

# Run migrations if artisan exists
if [ -f /var/www/html/artisan ] && [ -f /var/www/html/vendor/autoload.php ]; then
    echo "🗄️  Running database migrations..."
    php /var/www/html/artisan migrate --no-interaction || {
        echo "⚠️  Migration warning (may be already migrated or config issue)"
    }

    # Seed database if empty
    php /var/www/html/artisan db:seed --no-interaction 2>/dev/null || true

    # Create storage link
    php /var/www/html/artisan storage:link --no-interaction 2>/dev/null || true
fi

echo "✅ DEV setup complete! Starting PHP-FPM..."
echo "=========================================="

# Execute the main command
exec "$@"
