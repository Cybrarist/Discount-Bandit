#!/bin/sh

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer"
    composer install --no-interaction --no-progress
else
    composer dump-autoload
    composer update --no-interaction --no-progress
fi

# if the directory was created by the docker image, then remove it
# the system will copy .env.example later on.
if [ -d ".env" ]; then
    rm .env
fi

#if the database file was created by docker, then remove it and create new one.
if [ -d "/database/database.sqlite" ]; then
    rm database/database.sqlite
fi

touch database/database.sqlite


if [ ! -f "/logs" ]; then
    mkdir /logs
fi


if [ ! -f ".env" ] ||  ! grep -q . ".env" ; then
    cp .env.example .env
    php artisan key:generate --force
fi

php artisan storage:link

php artisan key:generate

printenv > /etc/environment

php artisan migrate --force --seed

php artisan optimize:clear

php artisan discount:fill-supervisor-workers

php artisan icons:cache

php artisan optimize

php artisan make:filament-user --name=$DEFAULT_USER --email=$DEFAULT_EMAIL --password=$DEFAULT_PASSWORD


php artisan octane:install --server=frankenphp

Xvfb :99 -screen 0 2000x2000x24 & export DISPLAY=:99

php artisan discount:test-notify

supervisord -c  /etc/supervisor/conf.d/supervisord.conf

