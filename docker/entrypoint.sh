#!/bin/sh
set -e

# Determine build target baked into the image (dev vs prod)
TARGET="${DOCKER_TARGET:-dev}"
cd /var/www/html

# --- Install/refresh dependencies (dev: source is bind-mounted) ------------
if [ -f composer.json ]; then
    if [ "$TARGET" = "prod" ]; then
        echo ">> [prod] composer deps already installed at build time"
    else
        echo ">> [dev] running composer install"
        composer install --no-interaction --prefer-dist
    fi
fi

# --- Ensure Laravel runtime directories exist -------------------------------
# dev bind-mounts the host tree which may lack these; prod image pre-creates
# them (owned by www-data), and this is a harmless no-op there.
mkdir -p \
    storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

# --- Generate APP_KEY if missing (works with no .env file) ------------------
# Export the value so if follows into every child process (artisan, serve,
# queue worker) without writing a .env file.
if [ -z "${APP_KEY}" ]; then
    echo ">> Generating application key"
    # PHP 8.5 prints PDO-deprecation notices to stdout, so filter out
    # everything except the base64:key line before exporting.
    export APP_KEY="$(php artisan key:generate --show 2>/dev/null | grep '^base64:' | tail -n 1)"
fi

# Storage + public symlink
php artisan storage:link --force 2>/dev/null || true

# --- Migrations (force in prod to avoid interactive prompts) ----------------
echo ">> Running migrations"
php artisan migrate --force

# --- Seed only when no real data present (dump/seed idempotency guard) ------
# DatabaseSeeder only creates users, labs and asset_type_codes; it never writes
# bookings/inventories. If bookings is non-empty, real (dump-imported) data is
# present, so skip seeding to keep that data authoritative across restarts.
if [ "$(php -r '$p=new PDO("mysql:host=".getenv("DB_HOST").";dbname=".getenv("DB_DATABASE"),getenv("DB_USERNAME"),getenv("DB_PASSWORD"));echo $p->query("SELECT COUNT(*) FROM bookings")->fetchColumn();' 2>/dev/null || echo 0)" -gt 0 ]; then
    echo ">> Existing data detected; skipping seed"
else
    echo ">> Seeding database"
    php artisan db:seed --force
fi

# --- Prod-only optimizations (seed reads env(), so cache after seeding) -----
if [ "$APP_ENV" = "production" ]; then
    echo ">> Caching config + routes (prod)"
    php artisan config:cache
    php artisan route:cache

    if [ -f mahasiswa_feb.csv ] && [ "$(php -r '$p=new PDO("mysql:host=".getenv("DB_HOST").";dbname=".getenv("DB_DATABASE"),getenv("DB_USERNAME"),getenv("DB_PASSWORD"));echo $p->query("SELECT COUNT(*) FROM mahasiswa_feb")->fetchColumn();' 2>/dev/null || echo 0)" -eq 0 ]; then
        echo ">> Importing mahasiswa data"
        php artisan import:mahasiswa
    fi
fi

# --- Start supervisor (laravel server + queue worker) ------------------------
echo ">> Starting supervisord"
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf