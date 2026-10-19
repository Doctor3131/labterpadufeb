# Docker deployment

The Docker stack consists of MariaDB, Laravel/PHP-FPM, and Nginx. Proxmox,
Tailscale, Cloudflared, Apache, and PM2 remain host-managed. PM2 is retained as
an application fallback; it is not a database fallback after cutover.

## Configuration and secrets

- Keep `.env.app`, `compose.env`, and `docker/secrets/` out of Git.
- `.env.app` contains Laravel's application key and non-database settings. For
  rehearsal, set `MAIL_MAILER=log` and do not add production integration tokens.
- `compose.env` contains only non-secret Compose values. Start Compose with
  `--env-file compose.env`; do not put these values in Laravel's `.env`.
- `LABTERPADU_STORAGE_APP_PATH` must point to an environment-specific storage
  directory. Rehearsal must use a separate copy, never the production path.
- `LABTERPADU_DB_VOLUME_NAME` must explicitly name the intended external Docker
  volume. Keep the rehearsal volume and production volume distinct.
- `LABTERPADU_IMAGE_TAG` should be the exact source commit used to build the
  images. Avoid `latest` or a reusable production tag.
- `LABTERPADU_WEB_PUBLISHED_PORT` controls the loopback-only Nginx port. Use
  `3334` for rehearsal; use `3333` in production only after PM2 releases it.
- PHP, Node, Composer, Nginx, and MariaDB base images are pinned by immutable
  registry digests; Composer and npm dependencies use their lockfiles.
- `LABTERPADU_BACKEND_INTERNAL=true` isolates PHP and migration containers from
  outbound network access during rehearsal. Nginx and MariaDB each have a
  separate non-internal network so Docker can publish their loopback-only host
  ports. Set the backend to `false` only for production after reviewing the
  application's required integrations.

Create the secrets directory and files with restrictive permissions. The PHP
container runs as UID 1000 and must be able to read only the application DB
password; the MariaDB container reads both secrets as root:

```sh
install -d -o root -g root -m 0700 docker/secrets
install -o 1000 -g 1000 -m 0400 /path/to/approved-db-password docker/secrets/db_password
install -o root -g root -m 0400 /path/to/approved-db-root-password docker/secrets/db_root_password
```

Do not place literal passwords in shell history or command arguments. The
existing rehearsal secrets are server-local and must not be copied into Git.

## Rehearsal

1. Copy `compose.env.example` to the ignored `compose.env`. Set its storage path
   to `/home/fadil/labterpadu-rehearsal-storage/app` and its volume name to the
   already-imported rehearsal DB volume. Keep the image tag tied to the source
   being rehearsed.
2. Copy production storage into that rehearsal directory without deleting
   destination-only files. Verify the source and destination are distinct and
   repeat an `rsync --dry-run` until there are no file changes to copy. The
   Laravel process runs as UID 1000, so the rehearsal directory must be writable
   by UID 1000; do not recursively change permissions on production storage.
3. Confirm the Compose config resolves to the rehearsal storage path, rehearsal
   database volume, and loopback-only ports (`127.0.0.1:3307` and
   `127.0.0.1:3334`). Confirm only the backend network is internal and that
   Nginx/DB are attached to their separate port-publishing networks. Do not
   start `app`, `migrate`, or `web` before this check.
4. Build the images, then start the rehearsal web service. Compose waits for the
   database health check, runs migrations against the selected Docker database,
   and only then starts PHP-FPM and Nginx.
5. Verify `/up`, the home page, CSS/JS assets, login, admin pages, document
   previews, and a representative form. Use test data only. Check logs for
   attempted external delivery and inspect memory/disk while the full stack is
   active. The image intentionally does not run the test suite.

Example commands (run from the project root):

```sh
rsync -a --numeric-ids \
  /home/fadil/LabterpaduFEB/storage/app/ \
  /home/fadil/labterpadu-rehearsal-storage/app/
chown 1000:1000 /home/fadil/labterpadu-rehearsal-storage/app
chmod 775 /home/fadil/labterpadu-rehearsal-storage/app
rsync -ani --out-format=%i \
  /home/fadil/LabterpaduFEB/storage/app/ \
  /home/fadil/labterpadu-rehearsal-storage/app/ | sort | uniq -c

docker compose --env-file compose.env config -q
docker compose --env-file compose.env build
docker compose --env-file compose.env up -d --wait --wait-timeout 180 web
docker compose --env-file compose.env ps
curl -fsS http://127.0.0.1:3334/up
```

The database is not exposed beyond loopback, and Nginx is not exposed beyond
loopback. Cloudflared remains pointed at the existing PM2 origin until a
separate, approved cutover.

## Production cutover gates

Do not switch public traffic until all of these are true:

- A current database dump and storage backup exist outside this VM; checksums
  match, and a restore has been verified.
- The final production dump is imported into a new Docker volume, not the
  rehearsal volume, and table counts and migration state are checked.
- Writes are paused during the final dump/import. No application queue worker,
  scheduler, or other writer is left unaccounted for.
- Compose migrations and health checks pass; the app, assets, uploads, and
  document previews have been checked locally.
- Build host-side Vite assets (`npm ci` and `npm run build`) for the PM2 fallback;
  verify there is no stale `public/hot` file. PM2 is valid JSON, starts
  successfully, and has been tested against the same Docker database and
  production storage that Docker uses.
- A read-only origin check confirmed Cloudflared currently reaches
  `127.0.0.1:3333`. Keep that route unchanged; set
  `LABTERPADU_WEB_PUBLISHED_PORT=3333` only after PM2 stops listening there.
  For app rollback, stop the Docker web service and restart PM2 on the same
  loopback port, still connected to the Docker database.
- RAM and disk headroom remain acceptable with all containers running.

At cutover, keep the host MariaDB and all old Docker volumes intact. Rollback to
PM2 is only an application rollback: PM2 must continue using the Docker DB so
that writes made after cutover are not lost. Do not point PM2 back to the old
host MariaDB unless a separate data-reconciliation procedure is approved.
