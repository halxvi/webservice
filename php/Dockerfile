FROM php:7.2-fpm-alpine3.7
COPY php.ini /usr/local/etc/php/
RUN  docker-php-ext-install pdo_mysql
RUN mkdir -p /var/www/html/css
RUN mkdir -p /var/www/html/vendor