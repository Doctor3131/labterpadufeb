#!/usr/bin/env bash
set -euo pipefail

# Deploy / update the production LabTerpaduFEB Docker stack on a server.
#
# Designed for VPS prod: baked image (no bind mounts), DB-backed sessions/queue.
# This script pulls the latest code, rebuilds the prod image, recreates the
# containers and runs a basic health check.
#
# Usage:
#   scripts/deploy-prod.sh               # pull + build + up + health-check
#   scripts/deploy-prod.sh /path/to/repo # from elsewhere, pass the repo dir
#
# Safety rules enforced here:
#   - never touches the database volumes: refuses `make clean` / `down -v`
#   - refuses to run against the dev overlay or with MODE=dev
#   - aborts instead of force-resetting if the worktree is dirty

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="${1:-$(dirname "$SCRIPT_DIR")}"
[ -f "$PROJECT_DIR/docker-compose.yml" ] || { echo "error: no docker-compose.yml in $PROJECT_DIR" >&2; exit 2; }
cd "$PROJECT_DIR"

[ "${MODE:-prod}" = "prod" ] || { echo "error: deploy runs in prod mode; MODE=dev refused" >&2; exit 2; }
[ -z "${1:-}" ] && [ -z "$(git -C "$PROJECT_DIR" config --get remote.origin.url 2>/dev/null)" ] && {
  echo "warn: no git remote detected; will not attempt to pull" >&2
}

ENVFILE="$PROJECT_DIR/.env.docker"
if [ ! -f "$ENVFILE" ] && [ -f "$PROJECT_DIR/.env.docker.example" ]; then
  echo "[deploy] creating $ENVFILE from example..."
  cp "$PROJECT_DIR/.env.docker.example" "$ENVFILE"
fi
[ -f "$ENVFILE" ] || { echo "error: $ENVFILE missing; create it from .env.docker.example" >&2; exit 2; }

echo "[deploy] pulling latest code (ff-only)"
if git -C "$PROJECT_DIR" rev-parse --git-dir >/dev/null 2>&1; then
  [ -z "$(git -C "$PROJECT_DIR" status --porcelain)" ] || { echo "error: worktree dirty, commit/stash first" >&2; exit 1; }
  git -C "$PROJECT_DIR" pull --ff-only
fi

echo "[deploy] building prod image"
docker compose --env-file "$ENVFILE" -f docker-compose.yml build

echo "[deploy] recreating stack"
docker compose --env-file "$ENVFILE" -f docker-compose.yml up -d --no-deps

PORT="$(sed -n 's/^APP_PORT=//p' "$ENVFILE" | tail -n 1)"; [ -n "$PORT" ] || PORT=3333

echo "[deploy] health-check http://localhost:$PORT"
ok=0
for _ in $(seq 1 30); do
  code="$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:$PORT/" 2>/dev/null)"
  if [ "$code" = "200" ]; then ok=1; break; fi
  sleep 2
done
[ "$ok" = "1" ] || { echo "[deploy] app did not return 200 within 60s; check: docker compose logs app" >&2; exit 1; }

echo "[deploy] OK — app is up."
echo "[deploy] notes:"
echo "  - data lives in named volumes labterpadu_db_data / labterpadu_storage;"
echo "  - never run 'make clean' or 'docker compose down -v' here (wipes data);"
echo "  - mahasiswa_feb.csv is gitignored — upload it manually if needed (import:mahasiswa);"
echo "  - add a cron job for scripts/backup.sh if not already scheduled."