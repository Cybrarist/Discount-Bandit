FROM php:8.2-apache

RUN apt-get update

# 1. development packages
RUN apt-get install -y \
    libmcrypt-dev \
    libbz2-dev \
    libzip-dev \
    libicu-dev \
    supervisor \
    cron \
     && apt-get clean && rm -rf /var/lib/apt/lists/*
# 2. apache configs + document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/discount-bandit/public
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_PID_FILE=/var/run/apache2.pid
ENV APACHE_LOCK_DIR=/var/lock/apache2
ENV APACHE_LOG_DIR=/var/log/apache2
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_RUN_USER=www-data


ENV DB_HOST=discount-bandit-mysql
ENV APP_PORT=80
ENV APP_URL=http://localhost:80
ENV APP_ENV=production
ENV APP_DEBUG=true

ENV DB_DATABASE=discount-bandit
ENV DB_PORT=3306
ENV DB_USERNAME=root
ENV DB_PASSWORD=password
ENV MYSQL_ROOT_PASSWORD=password

ENV NTFY_LINK="NTFY_LINK"
ENV DEFAULT_USER=Test
ENV DEFAULT_EMAIL=docker@test.com
ENV DEAFULT_PASSWORD=password

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

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /var/www/html/discount-bandit

RUN (crontab -l ; echo "*/5 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan schedule:run >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_ae >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_com >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_uk >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_de >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_fr >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_it >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_sa >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_es >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_pl >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_com_tr >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_com_au >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_com_br >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_ca >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_tr >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_eg >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_co_jp >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_in >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_com_mx >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_nl >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_sg >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_se >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=amazon_com_be >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=ebay_com >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=walmart_com >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=walmart_ca >> /dev/null 2>&1") | crontab - && \
    (crontab -l ; echo "*/6 * * * * /usr/local/bin/php /var/www/html/discount-bandit/artisan queue:work --stop-when-empty --queue=target_com >> /dev/null 2>&1") | crontab -


COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html/discount-bandit

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]







