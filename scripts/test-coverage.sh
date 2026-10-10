#!/usr/bin/env bash
set -uo pipefail

cd "$(dirname "$0")/.."

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

coverage_floor=53.35
test_storage=$(mktemp -d /tmp/labterpadu-coverage-storage.XXXXXX)
coverage_report=$(mktemp /tmp/labterpadu-coverage-report.XXXXXX)
mkdir -p "$test_storage/app/private" "$test_storage/app/public" \
    "$test_storage/framework/cache/data" "$test_storage/framework/sessions" \
    "$test_storage/framework/testing" "$test_storage/framework/views" "$test_storage/logs"

cleanup() {
    local exit_code=$?
    trap - EXIT
    case "$test_storage" in
        /tmp/labterpadu-coverage-storage.*) rm -rf -- "$test_storage" ;;
        *) printf 'Refusing to remove unexpected coverage storage path: %s\n' "$test_storage" >&2; exit_code=1 ;;
    esac
    case "$coverage_report" in
        /tmp/labterpadu-coverage-report.*) rm -f -- "$coverage_report" ;;
        *) printf 'Refusing to remove unexpected coverage report: %s\n' "$coverage_report" >&2; exit_code=1 ;;
    esac
    exit "$exit_code"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

export APP_ENV=testing
export DB_CONNECTION=sqlite
export DB_DATABASE=:memory:
export LARAVEL_STORAGE_PATH="$test_storage"

php artisan config:clear --ansi || exit $?
vendor/bin/phpunit --coverage-text="$coverage_report"
test_exit=$?

if [[ ! -s "$coverage_report" ]]; then
    printf '\nCoverage report was not generated. Check that Xdebug or PCOV is enabled.\n' >&2
    exit 1
fi

printf '\nCoverage summary (fast suite; app/ line coverage):\n'
sed -n '/^ Summary:/,/^$/p' "$coverage_report"

line_coverage=$(awk '/^  Lines:/ { gsub(/%/, "", $2); print $2; exit }' "$coverage_report")
if [[ -z "$line_coverage" ]]; then
    printf 'Could not read line coverage from PHPUnit report.\n' >&2
    exit 1
fi

printf 'Required minimum: %s%%\n' "$coverage_floor"
if ! awk -v actual="$line_coverage" -v floor="$coverage_floor" 'BEGIN { exit !(actual >= floor) }'; then
    printf 'Coverage ratchet failed: %s%% is below %s%%.\n' "$line_coverage" "$coverage_floor" >&2
    exit 1
fi

if [[ "$test_exit" -ne 0 ]]; then
    exit "$test_exit"
fi
