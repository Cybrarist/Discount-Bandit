#!/bin/sh

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer"
    composer install --no-interaction --no-progress
fi

cp .env.example .env

if [ ! -f "/logs" ]; then
    mkdir /logs
fi

php artisan storage:link

printenv > /etc/environment

php artisan migrate --force --seed

php artisan optimize:clear

php artisan filament:optimize-clear

php artisan octane:install --server=frankenphp

php artisan optimize

php artisan filament:optimize

php artisan discount:fill-supervisor-workers

php artisan discount:exchange-rate

Xvfb :99 -screen 0 2000x2000x24 & export DISPLAY=:99

#php artisan discount:test-notify

supervisord -c  /etc/supervisor/conf.d/supervisord.conf

