#!/usr/bin/env bash
set -euo pipefail

# Backup LabTerpaduFEB: database dump + storage volume, written as gzip files
# into BACKUP_DIR with an hourly timestamp. Old backups beyond KEEP are pruned.
#
# Usage:
#   scripts/backup.sh                 # prod stack, backups/ , keep last 7
#   scripts/backup.sh --dev           # use docker-compose.dev.yml overlay
#   scripts/backup.sh --keep 14 --backup-dir /var/backups/labterpadu
#
# Env overrides (same names, so cron can pass them): MODE BACKUP_DIR KEEP
set -u

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

MODE="${MODE:-prod}"
ENVFILE="$PROJECT_DIR/.env.docker"
[ -f "$ENVFILE" ] || ENVFILE="$PROJECT_DIR/.env"
BACKUP_DIR="${BACKUP_DIR:-$PROJECT_DIR/backups}"
KEEP="${KEEP:-7}"
COMPOSE_FILES=(-f "$PROJECT_DIR/docker-compose.yml")

while [ $# -gt 0 ]; do
  case "$1" in
    --dev) MODE=dev; shift ;;
    --keep) KEEP="$2"; shift 2 ;;
    --keep=*) KEEP="${1#--keep=}"; shift ;;
    --backup-dir) BACKUP_DIR="$2"; shift 2 ;;
    --backup-dir=*) BACKUP_DIR="${1#--backup-dir=}"; shift ;;
    *) echo "unknown flag: $1" >&2; exit 2 ;;
  esac
done

[ "$MODE" = "dev" ] && COMPOSE_FILES+=(-f "$PROJECT_DIR/docker-compose.dev.yml")
case "$KEEP" in
  ''|*[!0-9]*) echo "KEEP must be a number, got '$KEEP'" >&2; exit 2 ;;
esac

DB_NAME="$(: | sed -n "s/^DB_DATABASE=//p" "$ENVFILE" | tail -n 1)"
[ -z "$DB_NAME" ] && DB_NAME=labterpadu
STAMP="$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Quoted so the backtick/$ are expanded by the container's shell, not ours.
# DB_NAME is passed via -e; the MySQL root password is already an env var in
# the db container (root@localhost via the Unix socket, no TCP).
echo "[backup] mode=$MODE  dir=$BACKUP_DIR  keep=$KEEP"
docker compose --env-file "$ENVFILE" "${COMPOSE_FILES[@]}" exec -T \
  -e DB_NAME="$DB_NAME" db \
  sh -c 'mysqldump --no-tablespaces --single-transaction --routines --triggers \
         -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME"' \
  | gzip -9 > "$BACKUP_DIR/db-$STAMP.sql.gz"
echo "[backup] database -> $(basename "$BACKUP_DIR/db-$STAMP.sql.gz")"

STORAGE_VOL="${COMPOSE_PROJECT_NAME:-labterpadu}_storage"
if docker volume inspect "$STORAGE_VOL" >/dev/null 2>&1; then
  # shellcheck disable=SC2016  # $STAMP expands inside the container tar
  docker run --rm \
    -v "$STORAGE_VOL:/data:ro" \
    -v "$BACKUP_DIR:/backup" \
    -e STAMP="$STAMP" \
    alpine sh -c 'mkdir -p /backup && tar czf "/backup/storage-$STAMP.tar.gz" -C /data .'
  echo "[backup] storage   -> storage-$STAMP.tar.gz"
else
  echo "[backup] SKIP storage (volume '$STORAGE_VOL' not found)"
fi

# prune: keep the newest KEEP of each kind
prune() {
  local glob="$1" n=0
  for f in "$BACKUP_DIR"/$glob; do
    [ -e "$f" ] || continue
    printf '%s\n' "$f"
  done | sort -r | tail -n "+$((KEEP + 1))" | while IFS= read -r old; do
    rm -f -- "$old"; n=$((n + 1)); echo "[backup] pruned $(basename "$old")"
  done
}
prune 'db-*.sql.gz'
prune 'storage-*.tar.gz'

echo "[backup] done."