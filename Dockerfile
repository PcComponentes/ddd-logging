FROM php:8.0-cli-alpine3.13

RUN apk add --no-cache \
        libzip-dev \
        openssl-dev && \
    docker-php-ext-install -j$(nproc) \
        zip \
        bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN apk add --no-cache --virtual .phpize_deps $PHPIZE_DEPS && \
    pecl install xdebug-3.0.4 && \
    docker-php-ext-enable xdebug && \
    rm -rf /usr/share/php8 && \
    rm -rf /tmp/pear && \
    apk del .phpize_deps

ENV PATH /var/app/bin:/var/app/vendor/bin:$PATH

WORKDIR /var/app