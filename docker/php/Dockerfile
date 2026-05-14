FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libssl-dev \
    autoconf \
    g++ \
    make \
    pkg-config \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove autoconf g++ make pkg-config \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN printf "memory_limit=512M\nupload_max_filesize=64M\npost_max_size=64M\n" > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

COPY docker/php/entrypoint-dev.sh /usr/local/bin/entrypoint-dev
RUN chmod +x /usr/local/bin/entrypoint-dev

ENTRYPOINT ["entrypoint-dev"]
CMD ["php-fpm"]
