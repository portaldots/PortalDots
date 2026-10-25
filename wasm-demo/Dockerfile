FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip libonig-dev libxml2-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libsqlite3-dev libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j2 mbstring dom xmlwriter zip gd pdo_mysql pdo_sqlite bcmath intl \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
