FROM composer:2 AS vendor

WORKDIR /app/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

FROM php:8.3-cli-alpine

RUN docker-php-ext-install pdo_mysql

WORKDIR /app
COPY --from=vendor /app/backend/vendor ./backend/vendor
COPY backend ./backend
COPY database ./database

WORKDIR /app/backend
EXPOSE 8080

CMD ["sh", "-c", "php bin/init-database.php && php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
