FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# انسخ المشروع بالكامل الأول
COPY . .

# ثبت dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 10000

CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan serve --host=0.0.0.0 --port=10000
