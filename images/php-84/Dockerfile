# startitup-cloud/php/Dockerfile
FROM php:8.4-fpm

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libmemcached-dev \
    zlib1g-dev \
    libicu-dev \
    nano \
    libgmp-dev

RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    lsb-release \
    ca-certificates \
 && mkdir -p /etc/apt/keyrings \
 && curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg \
 && echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" \
    > /etc/apt/sources.list.d/docker.list \
 && apt-get update \
 && apt-get install -y docker-ce-cli \
 && rm -rf /var/lib/apt/lists/*

# Install and enable GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install and enable MySQL-related extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install and enable Memcached
#RUN pecl install memcached && docker-php-ext-enable memcached

# Install and enable Intl extension
RUN pecl intl && docker-php-ext-install intl

# Install and enable GMP extension
RUN docker-php-ext-install gmp

# Install and enable Redis
RUN pecl install -o -f redis && rm -rf /tmp/pear && docker-php-ext-enable redis

# PHP ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

COPY components /wordpress/components/

RUN chmod +x /wordpress/components/wp-cli.sh

RUN /wordpress/components/wp-cli.sh

WORKDIR /var/www/html
