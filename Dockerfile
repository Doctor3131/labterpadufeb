# syntax=docker/dockerfile:1

FROM php:8.4-fpm-bookworm@sha256:6bfef8e416977aa41f48e3e42a40c1e08050d24e4a938c6edb421400bff24601 AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libicu-dev \
        libonig-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j2 \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-labterpadu.ini

FROM php-base AS dependencies

COPY --from=composer:2@sha256:af98f42dfff7c68ba8d53c2164fd9fde1087b7d449514baa38c418b1f6bc4bac /usr/bin/composer /usr/local/bin/composer

COPY . .

RUN mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && composer install \
        --no-dev \
        --no-scripts \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --optimize-autoloader \
    && composer check-platform-reqs --no-dev

FROM node:22-bookworm-slim@sha256:c3de60bf2f9dd0ac6370e6117950ff62d6e339527e7472301c9c78a017978392 AS assets

WORKDIR /var/www/html

COPY --from=dependencies /var/www/html/ ./

RUN npm ci \
    && npm run build

FROM php-base AS app

COPY --from=dependencies /var/www/html/ /var/www/html/
COPY --from=assets /var/www/html/public/build/ /var/www/html/public/build/

COPY docker/entrypoint.sh /usr/local/bin/labterpadu-entrypoint

RUN groupmod --gid 1000 www-data \
    && usermod --uid 1000 --gid 1000 www-data \
    && ln -s /var/www/html/storage/app/public \
        /var/www/html/public/storage \
    && chmod 0755 /usr/local/bin/labterpadu-entrypoint \
    && chown -R www-data:www-data /var/www/html

RUN php-fpm -tt

USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/labterpadu-entrypoint"]

CMD ["php-fpm"]

FROM nginx:stable-alpine@sha256:970364228b1e4f932abc22d321932816782cca8a20b85a77552a7cfe9acfb3b5 AS web

COPY --from=app /var/www/html/public/ /var/www/html/public/
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

RUN nginx -t

EXPOSE 8080
