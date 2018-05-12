FROM php:7.2.2-fpm-alpine3.7

RUN apk upgrade --update && apk add \
libc-dev \
zlib-dev \
icu-dev \
autoconf \
gcc \
make

RUN docker-php-ext-install \
    intl \
    opcache

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# install redis ext
RUN pecl install redis \
    && docker-php-ext-enable redis
