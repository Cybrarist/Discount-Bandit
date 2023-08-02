FROM php:8.1-apache

RUN apt-get update

# 1. development packages
RUN apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip \
    iputils-ping \
    libzip-dev \
    libicu-dev \
    libbz2-dev \
    libpng-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libreadline-dev \
    libfreetype6-dev \
    g++ \
    cron  \
    supervisor \
    sudo




# 2. apache configs + document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/discount-bandit/public
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_PID_FILE=/var/run/apache2.pid
ENV APACHE_LOCK_DIR=/var/lock/apache2
ENV APACHE_LOG_DIR=/var/log/apache2
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_RUN_USER=www-data


ENV DB_HOST=mysql
ENV APP_PORT=8080
ENV APP_URL=http://localhost:8080
ENV DB_DATABASE=discount-bandit
ENV DB_USERNAME=bandit
ENV APP_ENV=prod
ENV DB_PASSWORD=banditPassword
ENV MYSQL_ROOT_PASSWORD=StrongPassword
ENV MAIL_MAILER=smtp
ENV MAIL_HOST=smtp.gmail.com
ENV MAIL_PORT=465
ENV MAIL_USERNAME=yournewemail@gmail.com
ENV MAIL_PASSWORD=VeryCompliatedPassword
ENV MAIL_ENCRYPTION=tls
ENV MAIL_FROM_ADDRESS=yournewemail@gmail.com
ENV MAIL_FROM_NAME=${APP_NAME}
ENV ALLOW_REF=1


RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. mod_rewrite for URL rewrite and mod_heade  rs for .htaccess extra headers like Access-Control-Allow-Origin-
RUN a2enmod rewrite headers

# 4. start with base php config, then add extensions
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN docker-php-ext-install \
    bz2 \
    intl \
    iconv \
    bcmath \
    opcache \
    calendar \
    pdo_mysql \
    zip

# 5. composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#RUN cp Docker/banditCron /etc/cron.d/banditCron
#RUN chmod 0644 /etc/cron.d/banditCron

RUN crontab -l | { cat; echo "* * * * *  cd /var/www/html/discount-bandit/ &&  /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --queue=products,grouplists --stop-when-empty>> /var/log/cron-test.log 2>&1"; } | crontab -
RUN crontab -l | { cat; echo "* * * * *   cd /var/www/html/discount-bandit/ && /usr/local/bin/php /var/www/html/discount-bandit/artisan schedule:run >> /var/log/cron-test.log 2>&1"; } | crontab -


#COPY Docker/supervisord.conf /etc/supervisor/supervisord.conf


WORKDIR /var/www/html
#
RUN sudo -u www-data git clone https://github.com/Cybrarist/discount-bandit.git discount-bandit/temp

#

WORKDIR /var/www/html/discount-bandit

RUN sudo -u www-data mv temp/*.* .

RUN sudo -u www-data mv temp/* .

RUN sudo chown -R www-data:www-data .

ENTRYPOINT ["Docker/entrypoint.sh"]


