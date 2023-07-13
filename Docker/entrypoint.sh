#!/bin/bash

if [ ! -f "vendor/autoload.php" ]; then
  echo "Installing Composer"
  sudo -u www-data composer install --no-interaction --no-progress
fi

if [ ! -f ".env" ]; then
    echo "Copying Environment File"
    cp .env.example .env
fi

php artisan migrate --seed

php artisan storage:link

php artisan key:generate
php artisan config:clear
php artisan route:clear
php artisan cache:clear

#cd ..
#echo "Started chown hidden files"
#chown -R www-data:www-data  .[^.]*
#echo "Started chown Directories"
#chown -R www-data:www-data  *

printenv > /etc/environment

sudo cron && /usr/sbin/apache2 -DFOREGROUND