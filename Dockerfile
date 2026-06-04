FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    mysql-client \
    nodejs \
    npm

RUN docker-php-ext-install pdo_mysql bcmath gd mbstring xml pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN composer dump-autoload --optimize

RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD sh -c "php artisan config:clear && \
           php artisan migrate --force && \
           php artisan db:seed --force && \
           php artisan optimize && \
           php -S 0.0.0.0:8000 -t public"