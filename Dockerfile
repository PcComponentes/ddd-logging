FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
        libzip-dev \
        openssl-dev && \
    docker-php-ext-install -j$(nproc) \
        zip \
        bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN apk add --update linux-headers &&\
    apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && apk del pcre-dev ${PHPIZE_DEPS}

ENV PATH /var/app/bin:/var/app/vendor/bin:$PATH

WORKDIR /var/app