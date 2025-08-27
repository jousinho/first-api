FROM php:8.2-fpm-alpine

ARG USER_ID
ARG GROUP_ID

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    libpq-dev \
    git \
    curl \
    unzip \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    opcache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN if [ ${USER_ID:-0} -ne 0 ] && [ ${GROUP_ID:-0} -ne 0 ]; then \
    deluser www-data && \
    addgroup -g ${GROUP_ID} jose && \
    adduser -u ${USER_ID} -G jose -s /bin/sh -D jose && \
    install -d -m 0755 -o jose -g jose /home/jose \
;fi

WORKDIR /var/www/project

USER jose