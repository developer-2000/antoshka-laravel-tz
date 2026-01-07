#!/bin/sh
set -e

echo "Waiting for MySQL to be ready..."
until nc -z mysql 3306 2>/dev/null; do
  echo "MySQL is unavailable - sleeping"
  sleep 2
done
echo "MySQL is ready"

echo "Waiting for Redis to be ready..."
until nc -z redis 6379 2>/dev/null; do
  echo "Redis is unavailable - sleeping"
  sleep 2
done
echo "Redis is ready"

echo "Installing Composer dependencies..."
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Убеждаемся, что все файлы кэша имеют правильные права
chown -R www-data:www-data /var/www/bootstrap/cache/* 2>/dev/null || true

echo "Clearing and caching configuration..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Предварительно создаем кэш маршрутов для избежания проблем при первом запросе
echo "Pre-caching routes..."
php artisan route:cache || true
# Исправляем права на кэш после создания
chown -R www-data:www-data /var/www/bootstrap/cache 2>/dev/null || true

if [ "$APP_ENV" != "local" ]; then
    echo "Caching for production..."
    php artisan config:cache
    php artisan view:cache
    # Исправляем права на кэш после создания
    chown -R www-data:www-data /var/www/bootstrap/cache
fi

echo "Configuring PHP-FPM to listen on 0.0.0.0:9000..."
sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf

# Убеждаемся, что кэш маршрутов существует и имеет правильные права (если был создан)
if [ -f "/var/www/bootstrap/cache/routes-v7.php" ]; then
    chown www-data:www-data /var/www/bootstrap/cache/routes-v7.php
    chmod 664 /var/www/bootstrap/cache/routes-v7.php
fi

echo "Starting PHP-FPM..."
exec php-fpm

