
FROM php:8.4-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html

COPY . .

RUN a2enmod rewrite

EXPOSE 80
