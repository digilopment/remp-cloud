FROM php:8.3-fpm

# --- NON-ROOT USER CONFIG ---
ARG UID=1000
ARG GID=1000
ARG UNAME=docker

RUN groupadd -g ${GID} -o ${UNAME} \
    && usermod -a -G ${UNAME} www-data \
    && useradd -m -u ${UID} -g ${GID} -o -s /bin/bash ${UNAME}

# --- BUILD & RUNTIME DEPENDENCIES ---
ENV BUILD_DEPS="libicu-dev libzip-dev zlib1g-dev libkrb5-dev libpng-dev g++ build-essential libssl-dev libsasl2-dev libmagickwand-dev libonig-dev"
ENV RUN_DEPS="git wget vim curl less gnupg unzip pv"

# --- INSTALL SYSTEM DEPENDENCIES ---
RUN apt-get update && apt-get install -y \
        $BUILD_DEPS $RUN_DEPS \
    --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

# --- PHP EXTENSIONS VIA MLOCATI ---
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        mysqli \
        pdo_mysql \
        bcmath \
        iconv \
        zip \
        intl \
        gd \
        soap \
        sockets \
        ftp \
        imap \
        opcache \
        imagick/imagick@master

# --- OPCACHE CONFIG ---
ADD opcache.ini /tmp/opcache.ini
RUN cat /tmp/opcache.ini >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# --- XDEBUG ---
RUN pecl install xdebug && docker-php-ext-enable xdebug
ADD xdebug.ini /tmp/xdebug.ini
RUN cat /tmp/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# --- PHP-FPM LOGGING CONFIG ---
ADD log.conf /usr/local/etc/php-fpm.d/zz-log.conf

# --- COMPOSER ---
RUN mkdir -p /composer/bin \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/composer/bin --filename=composer \
    && chown -R ${UID}:${GID} /composer
ENV COMPOSER_HOME=/composer
ENV PATH="/composer/bin:/composer/vendor/bin:${PATH}"
ENV COMPOSER_ALLOW_SUPERUSER=0

# --- APP DIRECTORY ---
RUN chmod 777 /var/www/html

USER ${UNAME}
