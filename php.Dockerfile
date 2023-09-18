FROM php:8.2-cli-alpine as composer
COPY ./install-composer.sh /tmp/install-composer.sh
RUN cd /tmp && sh /tmp/install-composer.sh



FROM php:8.2-cli-alpine as php

RUN apk add --no-cache --virtual .build-deps \
      $PHPIZE_DEPS \
      linux-headers \
      rabbitmq-c-dev \
    && apk add --no-cache rabbitmq-c \
    && pecl install amqp xdebug \
    && docker-php-ext-install sockets\
    && docker-php-ext-enable  \
      amqp \
      sockets \
      xdebug \
    && apk del .build-deps

COPY --from=composer /tmp/composer.phar /bin/composer
RUN chmod a+x /bin/composer

RUN mkdir /app
WORKDIR /app