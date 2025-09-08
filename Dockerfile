FROM dunglas/frankenphp:1.9.1-php8.4-trixie

LABEL authors="Cybrarist"

ENV SERVER_NAME=":80"
ENV FRANKENPHP_CONFIG="worker /app/public/index.php"
ENV FRANKEN_HOST="localhost"

RUN apt update && apt install -y supervisor  \
        libbz2-dev \
        libzip-dev \
        libmcrypt-dev \
        libicu-dev \
        gnupg \
        ca-certificates \
        libx11-xcb1 \
        libxcomposite1 \
        libxdamage1 \
        libxrandr2 \
        libatk1.0-0 \
        libnspr4 \
        libnss3 \
        libgtk-3-0 \
        libgbm-dev \
        libpango-1.0-0 \
        libatspi2.0-0 \
        libxshmfence1 \
        libxtst6 \
        chromium \
        chromium-driver \
        xvfb \
        xdg-utils \
        && apt-get clean



# Set environment variables for Chromium
ENV CHROME_BIN="/usr/bin/chromium-browser"
ENV CHROME_OPTS=" --disable-dev-shm-usage --headless --disable-gpu --no-sandbox --enable-features=ConversionMeasurement --remote-debugging-port=9222 "

RUN install-php-extensions @composer

RUN docker-php-ext-install   pcntl \
        opcache \
        pdo_mysql \
        pdo \
        bz2 \
        intl \
        iconv \
        bcmath \
        calendar \
        pdo_mysql \
        sockets \
        zip


COPY ./docker/base_supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY . /app

WORKDIR /app

EXPOSE 80 443 2019 8080


RUN chmod +x /app/*

RUN mkdir -p /config/chromium

ENV DISPLAY=:99

ENTRYPOINT ["docker/entrypoint.sh"]
