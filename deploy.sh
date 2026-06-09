#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# Shabakat Rewards — Production Deploy Script
# Run on your server after `git pull`:
#   bash deploy.sh
# ============================================================

APP_DIR="/var/www/shabakat"
BACKEND_DIR="$APP_DIR/backend"
USER_APP_DIR="$APP_DIR/user-app"
ADMIN_APP_DIR="$APP_DIR/admin-panel"

echo "=== 1. Install backend dependencies ==="
cd "$BACKEND_DIR"
composer install --no-dev --optimize-autoloader

echo "=== 2. Set up .env from template ==="
if [ ! -f .env ]; then
    cp .env.production .env
    php artisan key:generate
fi

echo "=== 3. Cache Laravel config & routes ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== 4. Run migrations ==="
php artisan migrate --force

echo "=== 5. Build user-app ==="
cd "$USER_APP_DIR"
npm ci
npm run build

echo "=== 6. Build admin-panel ==="
cd "$ADMIN_APP_DIR"
npm ci
npm run build

echo "=== 7. Set permissions ==="
cd "$BACKEND_DIR"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "=== 8. Reload PHP-FPM & queue ==="
sudo systemctl reload php8.2-fpm 2>/dev/null || true
cd "$BACKEND_DIR"
php artisan queue:restart 2>/dev/null || true

echo ""
echo "✅ Deploy complete!"
echo ""
echo "Next: configure nginx (see deploy/nginx.conf)"
