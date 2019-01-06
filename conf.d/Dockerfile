FROM php:7.1.10-fpm

LABEL maintainer="williamyan1024@gmail.com"

ADD ./sources.list /etc/apt/sources.list
RUN apt-get update \
    && apt-get install -y --assume-yes apt-utils libjpeg-dev libpng-dev \
    && docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
    && docker-php-ext-install gd \
    && pecl install -o -f redis \
    && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini


EXPOSE 9000
CMD ["php-fpm"]