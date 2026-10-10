#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ ! -f .env.dusk.local ]]; then
    printf 'Missing .env.dusk.local. Configure the dedicated local Dusk database first.\n' >&2
    exit 2
fi

# This is a read-only preflight. It rejects any existing tables/views before migrations.
php scripts/assert-dusk-database-empty.php

if php -r '$socket = @fsockopen("127.0.0.1", 8001, $error, $message, 0.2); if (! $socket) exit(1); fclose($socket);'; then
    printf 'Port 8001 is already in use; stopping rather than connecting Dusk to an unrelated local server.\n' >&2
    exit 1
fi

npm run build

backup_file=$(mktemp)
server_log=$(mktemp)
wipe_log=$(mktemp)
dusk_storage=$(mktemp -d /tmp/labterpadu-dusk-storage.XXXXXX)
mkdir -p "$dusk_storage/app/private" "$dusk_storage/app/public" \
    "$dusk_storage/framework/cache/data" "$dusk_storage/framework/sessions" \
    "$dusk_storage/framework/testing" "$dusk_storage/framework/views" "$dusk_storage/logs"
server_pid=""
had_env=0
database_may_have_changed=0

cleanup() {
    local exit_code=$?
    local cleanup_failed=0
    trap - EXIT

    if [[ -n "$server_pid" ]] && kill -0 "$server_pid" 2>/dev/null; then
        kill "$server_pid" 2>/dev/null || true
        wait "$server_pid" 2>/dev/null || true
    fi

    if [[ "$database_may_have_changed" -eq 1 ]]; then
        if ! php scripts/assert-dusk-config.php; then
            printf 'Skipping database cleanup because the runtime target is not the guarded Dusk database.\n' >&2
            cleanup_failed=1
        elif ! php artisan db:wipe --force --no-ansi >"$wipe_log" 2>&1; then
            printf 'Failed to empty the dedicated Dusk database after testing:\n' >&2
            cat "$wipe_log" >&2
            cleanup_failed=1
        elif ! php scripts/assert-dusk-database-empty.php; then
            printf 'Dusk database cleanup could not be verified.\n' >&2
            cleanup_failed=1
        fi
    fi

    if [[ "$had_env" -eq 1 ]]; then
        cp "$backup_file" .env
    else
        rm -f .env
    fi

    rm -f "$backup_file" "$server_log" "$wipe_log"
    case "$dusk_storage" in
        /tmp/labterpadu-dusk-storage.*) rm -rf -- "$dusk_storage" ;;
        *) printf 'Refusing to remove unexpected Dusk storage path: %s\n' "$dusk_storage" >&2; cleanup_failed=1 ;;
    esac

    if [[ "$cleanup_failed" -eq 1 && "$exit_code" -eq 0 ]]; then
        exit_code=1
    fi
    exit "$exit_code"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

if [[ -f .env ]]; then
    cp .env "$backup_file"
    had_env=1
fi

# Remove inherited app settings so the .env.dusk.local values, not the populated
# app .env or shell environment, determine the Dusk target.
unset APP_ENV APP_KEY APP_DEBUG APP_URL DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SOCKET FILESYSTEM_DISK CACHE_STORE SESSION_DRIVER QUEUE_CONNECTION MAIL_MAILER

# Laravel's storage_path() honors this before bootstrapping; all Dusk files/logs
# are confined to a fresh temporary directory, never the app's uploaded files.
export LARAVEL_STORAGE_PATH="$dusk_storage"
export DUSK_ALLOW_SCHEMA_RESET=1
cp .env.dusk.local .env
printf '\nLARAVEL_STORAGE_PATH=%s\n' "$dusk_storage" >> .env
php artisan config:clear --no-ansi
php scripts/assert-dusk-config.php
server_router="$PWD/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"
(cd public && exec php -d variables_order=EGPCS -S 127.0.0.1:8001 "$server_router") >"$server_log" 2>&1 &
server_pid=$!

ready=0
for _ in {1..30}; do
    if ! kill -0 "$server_pid" 2>/dev/null; then
        break
    fi

    if php -r '$socket = @fsockopen("127.0.0.1", 8001, $error, $message, 0.2); if (! $socket) exit(1); fclose($socket);'; then
        ready=1
        break
    fi
    sleep 1
done

if [[ "$ready" -ne 1 ]]; then
    printf 'Laravel test server did not start on 127.0.0.1:8001.\n' >&2
    cat "$server_log" >&2
    exit 1
fi

database_may_have_changed=1
php artisan dusk --without-tty --no-ansi
