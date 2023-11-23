#!/bin/bash

if [ ! -f "vendor/autoload.php" ]; then
  echo "Installing Composer"
  composer install --no-interaction --no-progress
fi

cp .env.example .env
php artisan storage:link
php artisan config:cache
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

php artisan key:generate --force

printenv > /etc/environment

php artisan migrate --seed --force

php artisan make:filament-user --name=$DEFAULT_USER --email=$DEFAULT_EMAIL --password=$DEAFULT_PASSWORD

chown -R www-data:www-data *  && chown -R www-data:www-data .*



cron && apache2 -DFOREGROUND
#
#if [ ! -f "vendor/autoload.php" ]; then
#  echo "Installing Composer"
#  composer install --no-interaction --no-progress
#fi
#
#cp .env.example .env
#
#
#php artisan key:generate --force
#php artisan storage:link
#php artisan config:cache
#php artisan config:clear
#php artisan cache:clear
#php artisan optimize:clear
#
#php artisan migrate --seed --force
#
#
##cd ..
##echo "Started chown hidden files"
##chown -R www-data:www-data  .[^.]*
##echo "Started chown Directories"
##chown -R www-data:www-data  *
#
##sudo printenv > /etc/environment
##
#php artisan make:filament-user --name=$DEFAULT_USER --email=$DEFAULT_EMAIL --password=$DEAFULT_PASSWORD
#
#
##cron && /usr/sbin/apache2 -DFOREGROUND
#
#
#sleep 10000
