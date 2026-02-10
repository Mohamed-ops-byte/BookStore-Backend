FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader \
 && php artisan storage:link || true \
 && chmod -R 775 storage bootstrap/cache \
 && php artisan optimize || true

CMD ["sh", "start.sh"]

