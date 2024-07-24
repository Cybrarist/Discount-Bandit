#!/bin/sh

cp .env.example .env

php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

php artisan key:generate --force

printenv > /etc/environment

php artisan migrate --seed --force

php artisan icons:cache
php artisan config:cache

supervisord -c  /etc/supervisor/conf.d/supervisord.conf
