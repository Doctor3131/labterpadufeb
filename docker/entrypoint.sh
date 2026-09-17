#!/bin/sh
# Entrypoint production: tunggu DB -> storage:link -> migrate --force -> serve.
# - migrate --force HANYA menjalankan migration yang belum jalan (idempotent,
#   bukan overwrite). Tidak pernah menjalankan migrate:fresh/refresh di sini.
# - Seed TIDAK otomatis (keputusan: manual via `docker compose exec` dari dump current).
set -eu

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

# 1. Tunggu MySQL siap (max ~60 detik), agar migrate tidak race dengan db start.
i=0
until php -r '$c = @fsockopen(getenv("DB_HOST") ?: "db", (int)(getenv("DB_PORT") ?: 3306)); if ($c) { fclose($c); exit(0); } exit(1);' 2>/dev/null; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "entrypoint: database ${DB_HOST}:${DB_PORT} tidak reachable setelah 60 detik, abort." >&2
        exit 1
    fi
    echo "entrypoint: menunggu database ${DB_HOST}:${DB_PORT}... (${i}/30)"
    sleep 2
done

# 1b. Fail-fast bila APP_KEY kosong/tidak valid. Tanpa ini Laravel hanya
#     melempar 500 "Unsupported cipher..." saat session/enkripsi dipakai.
php -r '$k = getenv("APP_KEY") ?: ""; if (!preg_match("/^base64:(?:[A-Za-z0-9+\/]{43}=|[A-Za-z0-9+\/]{22}==)$/", $k)) { fwrite(STDERR, "entrypoint: APP_KEY kosong/tidak valid — set APP_KEY valid (php artisan key:generate --show) di .env\n"); exit(1); }'

# 1c. Fail-fast bila folder writable tidak bisa ditulis (umumnya bind-mount milik
#     uid lain di host). Di host: `chown -R 10000:10000 ./data` (atau sesuaikan).
for _d in storage/app/public storage/app/private storage/logs storage/framework bootstrap/cache; do
    if [ ! -w "$_d" ]; then
        echo "entrypoint: ${_d} tidak writable oleh $(id -un) — perbaiki kepemilikan bind-mount di host (chown -R 10000:10000)." >&2
        exit 1
    fi
done

# 2. Pastikan symlink public/storage -> storage/app/public valid di dalam container.
#    (Repo membawa symlink absolut lama ke /var/www/html/...; recreate agar menunjuk
#    ke volume bind ./data/uploads yang di-mount.)
if [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

# 3. Cache config secukupnya tanpa membekukan DB yang salah (lihat AGENTS.md:
#    config:cache bisa mengunci kredensial lama — jadi clear dulu, cache belakangan).
php artisan config:clear --no-interaction --quiet || true

# 3b. Regenerate package manifest (composer install jalan --no-scripts di image,
#     dan bootstrap/cache/*.php dikecualikan dari image via .dockerignore).
php artisan package:discover --ansi --no-interaction || true

# 4. Migration pending saja. Aman diulang ("Nothing to migrate" jika sudah up-to-date).
php artisan migrate --force --no-interaction

exec "$@"
