#!/bin/bash

# =============================================================================
# Entrypoint Script untuk Aplikasi IKM v2.0.0
# =============================================================================

set -e

echo "🚀 Starting IKM Application v2.0.0..."

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
while ! mysqladmin ping -h"${DB_HOST:-mysql}" -P"${DB_PORT:-3306}" --silent; do
    sleep 2
done
echo "✅ MySQL is ready!"

# Wait for Redis to be ready
echo "⏳ Waiting for Redis to be ready..."
while ! redis-cli -h"${REDIS_HOST:-redis}" -p"${REDIS_PORT:-6379}" ping > /dev/null 2>&1; do
    sleep 1
done
echo "✅ Redis is ready!"

# Install PHP dependencies if composer.json exists
if [ -f "/var/www/html/composer.json" ]; then
    echo "📦 Installing PHP dependencies..."
    cd /var/www/html
    composer install --no-dev --optimize-autoloader --no-interaction
    echo "✅ Dependencies installed!"
fi

# Run database migrations
echo "🔄 Running database migrations..."
cd /var/www/html
php spark migrate --all
echo "✅ Migrations completed!"

# Set proper permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 777 /var/www/html/writable/cache
chmod -R 777 /var/www/html/writable/logs
chmod -R 777 /var/www/html/writable/session
chmod -R 777 /var/www/html/writable/uploads
echo "✅ Permissions set!"

# Clear cache
echo "🧹 Clearing cache..."
php spark cache:clear
echo "✅ Cache cleared!"

# Create necessary directories
mkdir -p /var/log/nginx
mkdir -p /var/log/supervisor
mkdir -p /run/nginx
mkdir -p /var/lib/redis

# Start Nginx
echo "🌐 Starting Nginx..."
nginx
echo "✅ Nginx started!"

# Start PHP-FPM
echo "☕ Starting PHP-FPM..."
php-fpm &
echo "✅ PHP-FPM started!"

# Start Redis (if not using external Redis)
if [ "${START_REDIS:-false}" = "true" ]; then
    echo "🔴 Starting Redis..."
    redis-server --daemonize yes
    echo "✅ Redis started!"
fi

# Start queue workers in background
echo "👷 Starting queue workers..."
for i in $(seq 1 ${QUEUE_WORKERS:-4}); do
    php spark queue:work --daemon &
    echo "   Worker $i started"
done
echo "✅ Queue workers started!"

echo ""
echo "================================================================="
echo "  🎉 IKM Application v2.0.0 is ready!"
echo "  📍 Access: http://localhost:${APP_PORT:-8080}"
echo "  📊 Prometheus: http://localhost:${PROMETHEUS_PORT:-9090}"
echo "  📈 Grafana: http://localhost:${GRAFANA_PORT:-3000}"
echo "================================================================="
echo ""

# Keep container running
exec "$@"
