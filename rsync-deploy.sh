#!/usr/bin/env bash

set -euo pipefail

# Usage:
#   ./rsync-deploy.sh <user@host> <remote_path> [ssh_port]
#
# Example:
#   ./rsync-deploy.sh deploy@192.168.1.10 /var/www/labterpadu 22
#
# Optional env vars:
#   SSH_KEY=/path/to/private_key
#   RSYNC_DELETE=1              # add --delete to mirror remote
#   POST_DEPLOY_CMD="cd /var/www/labterpadu && php artisan optimize"

if [[ $# -lt 2 ]]; then
  echo "Usage: $0 <user@host> <remote_path> [ssh_port]"
  exit 1
fi

REMOTE_HOST="$1"
REMOTE_PATH="$2"
SSH_PORT="${3:-22}"

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

SSH_OPTS="-p ${SSH_PORT}"
if [[ -n "${SSH_KEY:-}" ]]; then
  SSH_OPTS="${SSH_OPTS} -i ${SSH_KEY}"
fi

RSYNC_DELETE_FLAG=""
if [[ "${RSYNC_DELETE:-0}" == "1" ]]; then
  RSYNC_DELETE_FLAG="--delete"
fi

echo "Creating remote directory: ${REMOTE_PATH}"
ssh ${SSH_OPTS} "${REMOTE_HOST}" "mkdir -p '${REMOTE_PATH}'"

echo "Syncing project to ${REMOTE_HOST}:${REMOTE_PATH}"
rsync -azv ${RSYNC_DELETE_FLAG} \
  --exclude ".git/" \
  --exclude ".env" \
  --exclude "node_modules/" \
  --exclude "vendor/" \
  --exclude "storage/logs/" \
  --exclude "storage/framework/cache/" \
  --exclude "storage/framework/sessions/" \
  --exclude "storage/framework/views/" \
  --exclude "public/build/" \
  --exclude "public/hot" \
  --exclude "backups/" \
  --exclude "*.sql" \
  --exclude "*.log" \
  --exclude ".idea/" \
  --exclude ".vscode/" \
  -e "ssh ${SSH_OPTS}" \
  "${PROJECT_ROOT}/" "${REMOTE_HOST}:${REMOTE_PATH}/"

if [[ -n "${POST_DEPLOY_CMD:-}" ]]; then
  echo "Running remote post-deploy command"
  ssh ${SSH_OPTS} "${REMOTE_HOST}" "${POST_DEPLOY_CMD}"
fi

echo "Rsync deploy completed successfully"
