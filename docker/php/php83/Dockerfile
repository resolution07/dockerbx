FROM php:8.3-fpm-alpine

# Постоянные зависимости
RUN apk add --no-cache \
    icu-data-full \
    shadow \
    msmtp \
    perl \
    wget \
    procps \
    bash \
    libzip \
    libpng \
    libjpeg-turbo \
    libwebp \
    libxpm \
    libavif \
    freetype \
    icu \
    zlib \
    imagemagick \
    libgomp \
    bzip2 \
    libpq \
    libldap \
    libsasl \
    cyrus-sasl \
    gettext \
    gettext-libs \
    libxslt \
    libmemcached \
    libmemcached-libs \
    lz4 \
    liblzf \
    zstd \
    rabbitmq-c \
    libssh2

# Временные зависимости, PHP-расширения и PECL-расширения
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    linux-headers \
    icu-dev \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    libxpm-dev \
    libavif-dev \
    freetype-dev \
    bzip2-dev \
    postgresql-dev \
    openldap-dev \
    cyrus-sasl-dev \
    gettext-dev \
    libxml2-dev \
    libxslt-dev \
    libmemcached-dev \
    lz4-dev \
    liblzf-dev \
    zstd-dev \
    rabbitmq-c-dev \
    libssh2-dev \
    imagemagick-dev && \
    docker-php-ext-configure gd \
        --enable-gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
        --with-avif && \
    docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo_mysql \
        intl \
        exif \
        zip \
        bcmath \
        bz2 \
        calendar \
        gettext \
        ldap \
        pgsql \
        pdo_pgsql \
        shmop \
        sockets \
        sysvmsg \
        sysvsem \
        sysvshm \
        xsl && \
    pecl channel-update pecl.php.net && \
    for pkg in \
        amqp-2.2.0 \
        apcu-5.1.28 \
        igbinary-3.2.16 \
        imagick-3.8.1 \
        memcached-3.4.0 \
        msgpack-3.0.1 \
        redis-6.3.0 \
        ssh2-1.5.0 \
        xdebug-3.5.3; do \
        yes "" | pecl install "$pkg"; \
    done && \
    apk del .build-deps && \
    rm -rf /tmp/* /var/cache/apk/*

# Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Скрипты контейнера
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
COPY check-extensions.php /usr/local/bin/check-bitrix-extensions.php
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Отдельная директория для генерируемых ini-файлов,
# чтобы не перезаписывать смонтированный conf.d
RUN mkdir -p /usr/local/etc/php/conf.d-xdebug
ENV PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d:/usr/local/etc/php/conf.d-xdebug

# Настройка пользователя и прав
RUN usermod -u 1000 www-data \
    && chown -R www-data:www-data /var/www /usr/local/etc/php/conf.d-xdebug \
    && chmod 1777 /tmp

USER www-data
WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
