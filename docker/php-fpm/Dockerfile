FROM php:5.6.30-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    wget \
    zlib1g-dev \
    libmcrypt-dev \
    libzip-dev \
    php5-interbase

RUN cp /usr/lib/php5/20131226/* /usr/local/lib/php/extensions/no-debug-non-zts-20131226
RUN cp /etc/php5/mods-available/* /usr/local/etc/php/conf.d

WORKDIR /prof