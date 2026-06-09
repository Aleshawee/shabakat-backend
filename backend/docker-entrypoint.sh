#!/bin/bash
export MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
php artisan config:clear
php artisan migrate --force --no-interaction 2>&1
php bootstrap.php 2>&1
exec php -S 0.0.0.0:${PORT:-80} -t /var/www/html/public
