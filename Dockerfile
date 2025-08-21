FROM php:8.2-cli-alpine3.21

RUN apk add --no-cache \
        libzip-dev \
        openssl-dev && \
    docker-php-ext-install -j$(nproc) \
        zip \
        bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN apk add --no-cache --virtual .phpize_deps $PHPIZE_DEPS \
  && apk add linux-headers \
  && pecl install xdebug-3.4.5  \
  && docker-php-ext-enable xdebug \
  && apk del linux-headers ${PHPIZE_DEPS}

ENV PATH /var/app/bin:/var/app/vendor/bin:$PATH

WORKDIR /var/app