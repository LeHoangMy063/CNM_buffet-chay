FROM php:8.2-apache

RUN docker-php-ext-install mysqli
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/conf-available/buffet-chay.conf
RUN a2enconf buffet-chay

WORKDIR /var/www/html
