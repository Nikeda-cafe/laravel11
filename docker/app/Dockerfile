FROM php:8.2-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y \
        git \
        curl \
        libzip-dev \
        zip \
        unzip \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        mariadb-client \
        nodejs \
        npm \
    && docker-php-ext-install pdo_mysql bcmath pcntl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN groupadd -g ${GID} appgroup \
    && useradd -u ${UID} -g appgroup -m appuser

COPY docker/app/php.ini /usr/local/etc/php/conf.d/custom.ini

USER appuser
CMD ["php-fpm"]
