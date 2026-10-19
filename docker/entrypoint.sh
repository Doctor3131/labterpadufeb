#!/bin/sh
set -eu

umask 0002

if [ -n "${DB_PASSWORD_FILE:-}" ]; then
    if [ ! -r "$DB_PASSWORD_FILE" ]; then
        echo "Database password file is not readable." >&2
        exit 1
    fi

    DB_PASSWORD="$(cat "$DB_PASSWORD_FILE")"
    export DB_PASSWORD
    unset DB_PASSWORD_FILE
fi

mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

php artisan package:discover --ansi
php artisan config:cache

exec "$@"
