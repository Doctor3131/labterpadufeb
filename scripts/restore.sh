#!/usr/bin/env bash
set -euo pipefail

# Restore a LabTerpaduFEB backup created by scripts/backup.sh.
#
# Usage:
#   scripts/restore.sh [--dev] [--yes] <db.sql.gz|db.sql> [storage.tar.gz]
#
# Examples:
#   scripts/restore.sh backups/db-20260816-120000.sql.gz
#   scripts/restore.sh --dev --yes backups/db-20260816-120000.sql.gz backups/storage-20260816-120000.tar.gz
#
# Both restore steps are DESTRUCTIVE — the target database and storage volume
# are overwritten. The database is assumed to already exist (compose creates
# it); rows are replaced via DROP/CREATE-free full dump restore.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

MODE="${MODE:-prod}"
ENVFILE="$PROJECT_DIR/.env.docker"
[ -f "$ENVFILE" ] || ENVFILE="$PROJECT_DIR/.env"
ASSUME_YES=0
DBSQL=""
STORAGE_TGZ=""
COMPOSE_FILES=(-f "$PROJECT_DIR/docker-compose.yml")

for a in "$@"; do
  case "$a" in
    --dev) MODE=dev ;;
    --yes|-y) ASSUME_YES=1 ;;
    --help|-h) sed -n '2,20p' "$0"; exit 0 ;;
    --*) echo "unknown flag: $a" >&2; exit 2 ;;
    *) if [ -z "$DBSQL" ]; then DBSQL="$a"; else STORAGE_TGZ="$a"; fi ;;
  esac
done
[ "$MODE" = "dev" ] && COMPOSE_FILES+=(-f "$PROJECT_DIR/docker-compose.dev.yml")
[ -n "$DBSQL" ] || { echo "error: missing database dump file" >&2; exit 2; }
[ -f "$DBSQL" ] || { echo "error: no such file: $DBSQL" >&2; exit 2; }
[ -z "$STORAGE_TGZ" ] || { [ -f "$STORAGE_TGZ" ] || { echo "error: no such file: $STORAGE_TGZ" >&2; exit 2; }; }

DB_NAME="$(: | sed -n "s/^DB_DATABASE=//p" "$ENVFILE" | tail -n 1)"
[ -z "$DB_NAME" ] && DB_NAME=labterpadu
STORAGE_VOL="${COMPOSE_PROJECT_NAME:-labterpadu}_storage"

confirm() {
  if [ "$ASSUME_YES" = "1" ]; then return 0; fi
  printf 'Overwrite database "%s"%s? [y/N] ' "$DB_NAME" \
    "$([ -n "$STORAGE_TGZ" ] && echo " and volume $STORAGE_VOL" || echo "")"
  read -r ans
  [ "$ans" = "y" ] || [ "$ans" = "Y" ]
}

confirm || { echo "aborted."; exit 1; }

echo "[restore] restoring database $DB_NAME from $DBSQL"
if [[ "$DBSQL" == *.gz ]]; then
  gunzip -c "$DBSQL" | docker compose --env-file "$ENVFILE" "${COMPOSE_FILES[@]}" exec -T \
    -e DB_NAME="$DB_NAME" db \
    sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME"'
else
  docker compose --env-file "$ENVFILE" "${COMPOSE_FILES[@]}" exec -T \
    -e DB_NAME="$DB_NAME" db \
    sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME"' < "$DBSQL"
fi
echo "[restore] database OK"

if [ -n "$STORAGE_TGZ" ]; then
  echo "[restore] restoring volume $STORAGE_VOL from $STORAGE_TGZ"
  docker run --rm \
    -v "$STORAGE_VOL:/data" \
    -v "$(cd "$(dirname "$STORAGE_TGZ")" && pwd):/backup" \
    -e TGZ="$(basename "$STORAGE_TGZ")" \
    alpine sh -c 'tar xzf "/backup/$TGZ" -C /data'
  echo "[restore] storage OK"
fi

echo "[restore] done. Restart the app if it caches config/files: docker compose restart app"