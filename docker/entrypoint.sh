#!/bin/sh
set -e

echo "=========================================="
echo "🔧 KeywordAI Setup"
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
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Sync files from image to named volumes
# Named volumes overlay the image's directories, hiding new files from rebuilds.
# We stash fresh copies during build and sync them here on startup.
if [ -d /tmp/migrations-from-image ]; then
    echo "📋 Syncing migration files to database volume..."
    mkdir -p /var/www/html/database/migrations
    cp -n /tmp/migrations-from-image/*.php /var/www/html/database/migrations/ 2>/dev/null || true
fi

if [ -d /tmp/public-from-image ]; then
    echo "📋 Syncing public assets to shared volume..."
    cp -rf /tmp/public-from-image/* /var/www/html/public/ 2>/dev/null || true
    chown -R www-data:www-data /var/www/html/public
fi

# Ensure database file exists and is writable
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "📦 Creating SQLite database..."
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

# Ensure database is writable
chown www-data:www-data /var/www/html/database
chmod 775 /var/www/html/database

# Run migrations if artisan exists
if [ -f /var/www/html/artisan ]; then
    echo "🗄️  Running database migrations..."
    php /var/www/html/artisan migrate --force --no-interaction || {
        echo "⚠️  Migration warning (may be already migrated or config issue)"
    }
    
    # Cache config in production (but not in local dev)
    if [ "$APP_ENV" = "production" ]; then
        echo "⚡ Caching configuration..."
        php /var/www/html/artisan config:cache --no-interaction 2>/dev/null || true
        php /var/www/html/artisan route:cache --no-interaction 2>/dev/null || true
        php /var/www/html/artisan view:cache --no-interaction 2>/dev/null || true
    fi
    
    # Create storage link
    php /var/www/html/artisan storage:link --no-interaction 2>/dev/null || true
fi

echo "✅ Setup complete!"
echo "=========================================="
echo "Starting application..."
echo "=========================================="

# Execute the main command
exec "$@"
