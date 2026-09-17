# LabTerpaduFEB — Laravel 12 + Vite
# Tepat 3 stage: deps (composer prod-only) -> build (deps + node, vite asset,
# sekaligus image dev) -> runtime (minim, non-root, prod-only).
# Catatan secure-by-default: artisan serve bind 0.0.0.0:3333 memang dibutuhkan agar
# service nginx (container lain di network yang sama) bisa proxy_pass ke app.

# ---------------------------------------------------------------- Stage 1: deps
FROM php:8.4-cli-alpine3.21 AS deps

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

RUN apk add --no-cache git unzip

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Extension yang sama seperti runtime — dibutuhkan composer untuk platform check
# (mis. phpspreadsheet butuh ext-gd) saat `composer install`.
RUN apk add --no-cache libpng libjpeg-turbo freetype icu-libs libzip libxml2 \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS freetype-dev libjpeg-turbo-dev libpng-dev icu-dev libzip-dev libxml2-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring xml bcmath intl zip gd opcache pcntl \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# User khusus non-root, dibuat sekali di sini agar uid konsisten di semua stage.
RUN adduser -D -u 10000 -h /var/www/html -s /sbin/nologin appuser

WORKDIR /var/www/html

# Copy lockfile dulu agar layer install ter-cache selama dependency tidak berubah.
COPY composer.json composer.lock ./

# --no-scripts: artisan belum ada di stage ini (source belum di-copy).
RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-progress \
        --no-interaction \
        --optimize-autoloader \
        --no-scripts

# ---------------------------------------------------------------- Stage 2: build
# Turunan deps (sudah ada PHP + vendor prod) ditambah Node persis dari image
# node resmi (bukan nodejs apk yang versinya mengikuti Alpine), lalu Vite build.
# Stage ini juga dipakai sebagai image dev (compose.override.yaml) agar dev tools
# (composer + node + vendor) tersedia di dalam container.
FROM deps AS build

RUN apk add --no-cache libstdc++ \
    && rm -rf /var/cache/apk/*

COPY --from=node:22-alpine3.21 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-alpine3.21 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && node --version && npm --version

USER appuser

# Copy lockfile dulu agar `npm ci` ter-cache.
COPY --chown=appuser:appuser package.json package-lock.json ./

RUN npm ci --no-audit --no-fund

# Hanya file yang dibutuhkan Vite untuk bundling (bukan seluruh repo).
COPY --chown=appuser:appuser vite.config.js ./
COPY --chown=appuser:appuser resources/ ./resources/
COPY --chown=appuser:appuser public/ ./public/

RUN npm run build

# ---------------------------------------------------------------- Stage 3: runtime
FROM php:8.4-cli-alpine3.21 AS runtime

ENV APP_ENV=production \
    LOG_CHANNEL=stderr \
    # Upload user TIDAK dibake ke image — lihat compose.yaml (bind ./data).
    COMPOSER_ALLOW_SUPERUSER=0

# Extension Laravel yang dibutuhkan: pdo_mysql (DB), mbstring/xml (framework),
# bcmath (validasi/angka), intl (Carbon/format Indonesia), zip (phpspreadsheet),
# gd (ItemImageService re-encode + dompdf), opcache + pcntl (runtime).
# Build-dep dihapus di RUN yang sama agar tidak tersisa di layer akhir.
RUN apk add --no-cache libpng libjpeg-turbo freetype icu-libs libzip libxml2 \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS freetype-dev libjpeg-turbo-dev libpng-dev icu-dev libzip-dev libxml2-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring xml bcmath intl zip gd opcache pcntl \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

RUN adduser -D -u 10000 -h /var/www/html -s /sbin/nologin appuser

WORKDIR /var/www/html

# Artifact dari dua stage sebelumnya (tidak install ulang di stage akhir).
COPY --from=deps /var/www/html/vendor ./vendor
COPY --from=build /var/www/html/public/build ./public/build

# Source aplikasi (isi upload user + cache bootstrap dikecualikan via .dockerignore).
# NOTE: satu COPY per direktori dengan dest eksplisit — `COPY a/ b/ ./` justru
# melebur ISI semua direktori ke ./ (gotcha Docker), bukan mempertahankan namanya.
COPY artisan ./
# composer.json dibutuhkan Laravel saat runtime (Application::getNamespace()).
COPY composer.json ./
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY database/ ./database/
COPY resources/ ./resources/
COPY routes/ ./routes/
COPY public/ ./public/
COPY app/ ./app/

# Folder writable Laravel disiapkan + dimiliki appuser.
RUN mkdir -p storage/app/public storage/app/private \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
        storage/logs bootstrap/cache \
    && chown -R appuser:appuser storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Symlink public/storage dibuat saat BUILD (bukan di entrypoint): container jalan
# read-only sehingga `php artisan storage:link` gagal di runtime. Target absolut
# /var/www/html/... tetap valid karena path container tidak berubah.
RUN php artisan storage:link --no-interaction \
    && chown -h appuser:appuser public/storage || true

COPY --chown=appuser:appuser docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER appuser

EXPOSE 3333

# Route bawaan Laravel 12 (bootstrap/app.php: health: '/up').
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD wget -qO- http://127.0.0.1:3333/up > /dev/null 2>&1 || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=3333", "--no-reload"]
