FROM composer:2 AS composer

WORKDIR /app
COPY . /app

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

FROM node:20 AS node

WORKDIR /app
COPY . /app

RUN npm install
RUN npm run build

FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer /app /var/www/html
COPY --from=node /app/public/build /var/www/html/public/build

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

RUN rm -f /var/www/html/bootstrap/cache/config.php \
    /var/www/html/bootstrap/cache/routes-v7.php \
    /var/www/html/bootstrap/cache/events.php \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs

RUN test -f /var/www/html/certs/isrgrootx1.pem

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

CMD sh -c 'sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf && sed -i "s/:80/:${PORT:-10000}/" /etc/apache2/sites-available/000-default.conf && apache2-foreground'