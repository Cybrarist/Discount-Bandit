FROM dunglas/frankenphp:latest-builder-php8.3.7-bookworm

LABEL authors="Cybrarist"

ENV SERVER_NAME=":80"
ENV FRANKENPHP_CONFIG="worker /app/public/index.php"


RUN apt update && apt install -y supervisor \
        libmcrypt-dev \
        libbz2-dev \
        libzip-dev \
        libicu-dev

RUN docker-php-ext-install   pcntl \
        opcache \
        pdo_mysql \
        pdo \
        bz2 \
        intl \
        iconv \
        bcmath \
        opcache \
        calendar \
        pdo_mysql \
        zip

COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY . /app

WORKDIR /app

EXPOSE 80 443 2019 8080

RUN chmod +x /app/*

ENTRYPOINT ["docker/entrypoint.sh"]
