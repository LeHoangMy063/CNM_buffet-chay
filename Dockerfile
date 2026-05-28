FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install mysqli \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/conf-available/buffet-chay.conf
RUN a2enconf buffet-chay

WORKDIR /var/www/html
