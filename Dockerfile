# ---- Stage 1: build frontend assets (Vite) ----
FROM node:22-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---- Stage 2: PHP-FPM + Nginx runtime ----
FROM php:8.3-fpm

# System deps + PHP extensions (MySQL + Laravel requirements)
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        nginx \
        supervisor \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Config files (nginx, php.ini, supervisord, entrypoint)
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-campus.ini
COPY docker/nginx.conf /etc/nginx/sites-enabled/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/campus-entrypoint
RUN chmod +x /usr/local/bin/campus-entrypoint

# Application source (vendor/.env excluded via .dockerignore)
COPY . .

# Composer deps (no dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/private/uploads \
    && chown -R www-data:www-data storage bootstrap/cache

# Compiled Vite assets for Blade admin/login pages
COPY --from=assets /build/public/build /var/www/html/public/build

EXPOSE 80

CMD ["/usr/local/bin/campus-entrypoint"]
