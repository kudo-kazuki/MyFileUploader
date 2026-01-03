#!/usr/bin/env bash
set -e

echo "[PHP Entrypoint] Development mode"

# 初回だけ composer install（明示的）
if [ ! -d "/var/www/html/vendor" ] && [ -f "/var/www/html/composer.lock" ]; then
  echo "Installing dependencies..."
  composer install --no-interaction --prefer-dist
fi

# uploads ディレクトリだけ権限調整
if [ -d "/var/www/html/uploads" ]; then
  chown -R www-data:www-data /var/www/html/uploads || true
fi

echo "[PHP Entrypoint] Starting php-fpm"
exec docker-php-entrypoint php-fpm
