#!/usr/bin/env bash
#
# db-import.sh — import a SQL dump into the MySQL container (dockerized app).
#
# Usage:
#   ./db-import.sh <file.sql> [<extra docker compose args...>]
#   ./db-import.sh -h | --help
#
# Interactables that make it CI-friendly (override skip-prompt):
#   DB_USER, DB_PASS, DB_NAME, DB_HOST   database credentials
#   COMPOSE_FILES                        full compose -f args (ignores extra args)
#   MODE=dev                             add the dev overlay compose file by default
#
# Examples:
#   ./db-import.sh labterpadu-10-05-2026.sql
#   ./db-import.sh dump.sql -f docker-compose.dev.yml
#   DB_USER=root DB_PASS=x DB_NAME=labterpadu ./db-import.sh dump.sql --force
#
# NOTE: the dump is destructive — it DROP-CREATEs the tables it contains.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

usage() {
  local rc="${1:-0}"
  cat <<EOF
Usage: $0 <file.sql> [<extra docker compose args...>]
       $0 -h | --help

Import a SQL dump into the 'db' container via stdin (no temp file inside).

Interactables (env vars skip the matching prompt / avoid blocking in CI):
  DB_USER  DB_PASS  DB_NAME  DB_HOST
  COMPOSE_FILES                full -f args (overrides positional + MODE)
  MODE=dev                     default: add the dev compose overlay

Options:
  -f | --force   skip the interactive confirmation (destructive import)
  -h | --help    show this help and exit
EOF
  exit "$rc"
}

FORCE=0
POSITIONAL=()
for arg in "$@"; do
  case "$arg" in
-h|--help) usage 0 ;;
  -f|--force) FORCE=1 ;;
    -*) POSITIONAL+=("$arg") ;;
    *) POSITIONAL+=("$arg") ;;
  esac
done

[ "${#POSITIONAL[@]}" -ge 1 ] || { echo "[db-import] error: missing <file.sql>" >&2; usage 1; }

FILE="${POSITIONAL[0]}"
EXTRA_COMPOSE_OPTS=("${POSITIONAL[@]:1}")

# --- Validate the dump file ------------------------------------------------
[ -e "$FILE" ] || { echo "[db-import] error: file not found: $FILE" >&2; exit 1; }
[ -r "$FILE" ] || { echo "[db-import] error: file not readable: $FILE" >&2; exit 1; }
[ -s "$FILE" ] || { echo "[db-import] error: file is empty: $FILE" >&2; exit 1; }
if [[ "$FILE" != *.sql ]]; then
  echo "[db-import] warning: file does not end in .sql — continuing anyway"
fi

# --- Warn if the dump predates the app's migrations (stale-import footgun) ---
# A dump taken from older code has fewer recorded migrations than the current
# repo; on the next boot the entrypoint runs the newer migrations on top of the
# already-populated (newer) schema and can fail. Surface this before importing.
if [ -d "$SCRIPT_DIR/database/migrations" ]; then
  REPO_LATEST="$(find "$SCRIPT_DIR/database/migrations" -maxdepth 1 -name '*.php' -printf '%f\n' 2>/dev/null \
    | grep -oE '^[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}' | sort | tail -n 1)"
  # shellcheck disable=SC2016  # quoted to keep the backticks literal
  DUMP_LATEST="$(grep -F 'INSERT INTO `migrations`' "$FILE" 2>/dev/null \
    | grep -oE '[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}' | sort | tail -n 1)"
  if [ -n "$REPO_LATEST" ] && [ -n "$DUMP_LATEST" ] && [ "$DUMP_LATEST" \< "$REPO_LATEST" ]; then
    echo "[db-import] WARNING: dump migrations ($DUMP_LATEST) are older than the app's ($REPO_LATEST)."
    echo "  On next boot, migrate will apply newer migrations on the already-populated schema and may fail."
    echo "  Prefer a dump from current code; or re-import + migrate on a clean database."
  fi
fi

# --- Resolve compose args (COMPOSE_FILES env > positional > MODE) -----------
if [ -n "${COMPOSE_FILES:-}" ]; then
  read -r -a COMPOSE_ARGS <<<"$COMPOSE_FILES"
elif [ "${#EXTRA_COMPOSE_OPTS[@]}" -gt 0 ]; then
  COMPOSE_ARGS=("${EXTRA_COMPOSE_OPTS[@]}")
else
  COMPOSE_ARGS=("-f" "docker-compose.yml")
  if [ "${MODE:-prod}" = "dev" ]; then
    COMPOSE_ARGS+=("-f" "docker-compose.dev.yml")
  fi
fi

# --- Load defaults from .env.docker for prompt hints ------------------------
ENV_DOCKER="$SCRIPT_DIR/.env.docker"
get_env() { grep -E "^$1=" "$ENV_DOCKER" 2>/dev/null | tail -n 1 | cut -d= -f2- | tr -d '"' || true; }

DEFAULT_HOST="db"
DEFAULT_USER="labterpadu"
DEFAULT_NAME="labterpadu"
if [ -f "$ENV_DOCKER" ]; then
  DEFAULT_HOST="${DB_HOST:-$(get_env DB_HOST)}"
  DEFAULT_HOST="${DEFAULT_HOST:-db}"
  DEFAULT_USER="$(get_env DB_USERNAME)";       DEFAULT_USER="${DEFAULT_USER:-labterpadu}"
  DEFAULT_NAME="$(get_env DB_DATABASE)";       DEFAULT_NAME="${DEFAULT_NAME:-labterpadu}"
fi

# --- Gather credentials -----------------------------------------------------
# Prompts only run on a real TTY; in non-interactive mode (CI/pipe) the env
# vars must be set, otherwise we abort instead of blocking on EOF.
IS_TTY=0; [ -t 0 ] && IS_TTY=1
DB_HOST="${DB_HOST:-$DEFAULT_HOST}"
DB_USER="${DB_USER:-}"
DB_NAME="${DB_NAME:-}"

if [ "$IS_TTY" -eq 1 ]; then
  read -rp "DB host [$DEFAULT_HOST]: " DB_HOST; DB_HOST="${DB_HOST:-$DEFAULT_HOST}"
  read -rp "DB user [$DEFAULT_USER]: "  DB_USER; DB_USER="${DB_USER:-$DEFAULT_USER}"
  read -rp "DB name [$DEFAULT_NAME]: "  DB_NAME; DB_NAME="${DB_NAME:-$DEFAULT_NAME}"
  if [ -z "${DB_PASS:-}" ]; then
    read -rsp "DB password for ${DB_USER}: " DB_PASS; echo
  fi
else
  if [ -z "${DB_USER:-}" ] || [ -z "${DB_NAME:-}" ] || [ -z "${DB_PASS:-}" ]; then
    echo "[db-import] error: non-interactive stdin but missing DB_USER/DB_NAME/DB_PASS (set env vars)" >&2
    exit 1
  fi
fi

[ -n "$DB_PASS" ] || { echo "[db-import] error: empty password (set DB_PASS to skip prompt)" >&2; exit 1; }

# --- Confirm ----------------------------------------------------------------
SIZE="$(du -h "$FILE" | cut -f1)"
echo
echo "[db-import] Ready to import:"
echo "  file     : $FILE ($SIZE)"
echo "  target   : $DB_HOST / $DB_NAME (user: $DB_USER)"
echo "  compose  : ${COMPOSE_ARGS[*]}"
echo "  note     : destructive — DROP-CREATEs tables in the dump."
echo
if [ "$FORCE" -ne 1 ]; then
  if ! read -rp "Continue? [y/N] " CONFIRM; then CONFIRM=""; fi
  [[ "$CONFIRM" =~ ^[Yy]$ ]] || { echo "Aborted."; exit 1; }
fi

# --- Import (creds via -e env, not argv; stdin, no temp file) ---------------
trap 'echo "[db-import] failed at line ${LINENO}" >&2; exit 1' ERR
echo "[db-import] importing $FILE ..."
docker compose --env-file .env.docker "${COMPOSE_ARGS[@]}" exec -T \
  -e DBUSER="$DB_USER" -e DBPASS="$DB_PASS" -e DBNAME="$DB_NAME" \
  db sh -c 'mysql -u"$DBUSER" -p"$DBPASS" "$DBNAME"' < "$FILE"
trap - ERR

echo "[db-import] import completed successfully."