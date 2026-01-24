# ビルドステージ
FROM php:8.2-fpm-alpine AS builder

# Composerのインストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.jsとnpmのインストール（アセットビルド用）
RUN apk add --no-cache \
    nodejs \
    npm \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    libpng-dev \
    libxml2-dev \
    mariadb-client \
    pkgconfig \
    build-base

# PHP拡張機能のインストール
RUN docker-php-ext-install \
    pdo_mysql \
    bcmath \
    pcntl \
    zip \
    gd \
    opcache

WORKDIR /var/www/html

# Composer依存関係のインストール
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# アプリケーションコードのコピー
COPY . .

# Composerスクリプトの実行（autoloadの最適化など）
RUN composer dump-autoload --optimize --classmap-authoritative

# アセットのビルド（package.jsonは既にCOPY . .でコピー済み）
RUN npm ci && npm run build && rm -rf node_modules

# 本番ステージ
FROM php:8.2-fpm-alpine

# 必要なパッケージのインストール（開発用パッケージも一時的に必要）
RUN apk add --no-cache \
    libzip \
    libzip-dev \
    oniguruma \
    oniguruma-dev \
    libpng \
    libpng-dev \
    libxml2 \
    libxml2-dev \
    mariadb-client \
    freetype \
    freetype-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    pkgconfig \
    build-base

# PHP拡張機能のインストール
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    bcmath \
    pcntl \
    zip \
    gd \
    opcache

# 開発用パッケージを削除（イメージサイズを小さくする）
RUN apk del \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    libxml2-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    pkgconfig \
    build-base

# OPcache設定の最適化
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# PHP設定の最適化
RUN echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=32M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=32M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time=60" >> /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# ビルドステージからファイルをコピー
COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

# ストレージとブートストラップキャッシュのディレクトリを作成
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# 非rootユーザーで実行
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
