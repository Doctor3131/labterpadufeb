# syntax=docker/dockerfile:1

# =============================================================================
# Stage 1: node builder — compiles production assets (only used by the prod target)
# =============================================================================
FROM node:22-alpine AS node

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# =============================================================================
# Stage 2: base runtime — PHP + required extensions + Composer + supervisor
# =============================================================================
# composer.lock resolves to Symfony 8 deps which require PHP >= 8.4, so we use
# the PHP 8.5 runtime to match the versions locked on the host.
FROM php:8.5-cli AS base

# System deps needed to build PHP extensions + run Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip zip curl \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        supervisor \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions required by the app (Laravel 12, dompdf, phpspreadsheet).
# NOTE: opcache is bundled into the CLI image and must NOT be listed here
# (docker-php-ext-install opcache fails on PHP 8.5); it is tuned via opcache.ini.
RUN docker-php-ext-configure gd \
        --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Composer (official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure the PHP CLI built-in server to use opcache in production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Supervisor + entrypoint
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 3333

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# -----------------------------------------------------------------------------
# Target: dev — source is bind-mounted at runtime; install deps in entrypoint
# -----------------------------------------------------------------------------
FROM base AS dev

ENV DOCKER_TARGET=dev

# no-op; code/deps come from bind mounts + entrypoint
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# -----------------------------------------------------------------------------
# Target: prod — bake application code + Composer deps + built assets
# -----------------------------------------------------------------------------
FROM base AS prod

ENV DOCKER_TARGET=prod

# Copy app source (respects .dockerignore; excludes vendor/node_modules/.env)
COPY . .

# Copy built production assets from the node builder stage
COPY --from=node /build/public/build ./public/build

# Install production Composer dependencies (no dev deps, optimized)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Laravel runtime directories (bootstrap/cache is where config/route cache land)
# Note: bootstrap/cache/* is dockerignored so the baked dir starts clean.
RUN mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache \
        storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rw,o-w storage bootstrap/cache

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]