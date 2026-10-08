#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

test_storage=$(mktemp -d /tmp/labterpadu-phpunit-storage.XXXXXX)
mkdir -p "$test_storage/app/private" "$test_storage/app/public" \
    "$test_storage/framework/cache/data" "$test_storage/framework/sessions" \
    "$test_storage/framework/testing" "$test_storage/framework/views" "$test_storage/logs"

cleanup() {
    local exit_code=$?
    trap - EXIT
    case "$test_storage" in
        /tmp/labterpadu-phpunit-storage.*) rm -rf -- "$test_storage" ;;
        *) printf 'Refusing to remove unexpected PHPUnit storage path: %s\n' "$test_storage" >&2; exit_code=1 ;;
    esac
    exit "$exit_code"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# Force the CLI runner and test workers onto isolated SQLite and temporary storage,
# even when the developer's normal .env is configured for populated MySQL/storage.
export APP_ENV=testing
export DB_CONNECTION=sqlite
export DB_DATABASE=:memory:
export LARAVEL_STORAGE_PATH="$test_storage"

php artisan config:clear --ansi
php artisan test "$@"
