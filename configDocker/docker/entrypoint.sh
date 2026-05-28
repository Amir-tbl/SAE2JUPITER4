#!/bin/bash
set -e

# Start MariaDB
service mariadb start

# Create DB and dedicated user
mariadb -e "CREATE DATABASE IF NOT EXISTS suivi_colis_iutv;"
mariadb -e "CREATE USER IF NOT EXISTS 'app_colis'@'localhost' IDENTIFIED BY 'app_colis';"
mariadb -e "GRANT ALL PRIVILEGES ON suivi_colis_iutv.* TO 'app_colis'@'localhost';"
mariadb -e "FLUSH PRIVILEGES;"

# Install deps from volume-mounted code
cd /var/www/suivi-colis-iutv
composer update --optimize-autoloader

# Generate app key if missing
php artisan key:generate --force

# Run migrations
php artisan migrate --force 2>&1 || echo "Migration failed, check DB connection"

# Start Apache in foreground (exec replaces shell as PID 1)
exec apache2ctl -D FOREGROUND
