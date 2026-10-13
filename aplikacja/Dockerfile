# syntax=docker/dockerfile:1

# ---------- Stage 1: build frontend assets (Vite + Tailwind) ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- Stage 2: PHP runtime (Apache + mod_php) ----------
FROM php:8.3-apache

# System libs + PHP extensions (pdo_sqlite and mbstring ship with the base image)
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip libzip-dev libicu-dev sqlite3 \
    && docker-php-ext-install opcache zip intl pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Production php.ini + opcache tuning
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

# Serve Laravel's public/ directory, enable .htaccess rewrites
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first (cached layer as long as composer.lock doesn't change)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# Application code + built assets
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Container-friendly defaults; override at runtime as needed
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/storage/app/database.sqlite

VOLUME ["/var/www/html/storage"]
EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
