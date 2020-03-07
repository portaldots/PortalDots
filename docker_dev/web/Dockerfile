FROM php:7.3-apache
ADD ./php/php.ini /usr/local/etc/php/
ADD ./apache/*.conf /etc/apache2/sites-enabled/

RUN apt-get update \
    && apt-get install -y zlib1g-dev libpq-dev libzip-dev default-mysql-client unzip wget \
    && pecl install xdebug \
    && docker-php-ext-install zip pdo_mysql mysqli \
    && docker-php-ext-enable mysqli xdebug

RUN a2enmod rewrite

RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet \
    && mv composer.phar /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1

ENV COMPOSER_HOME /composer

ENV PATH $PATH:/composer/vendor/bin

WORKDIR /var/www/html
