# Docker deployment

The Docker stack consists of MariaDB, Laravel/PHP-FPM, and Nginx. Proxmox,
Tailscale, Cloudflared, Apache, and PM2 remain host-managed. PM2 is retained as
an application fallback; it is not a database fallback after cutover.

## Current production topology (verified 2026-10-20)

- Compose project: `labterpadu-prod-docker-20261020`, deployed from the
  `infra/docker-production` source at image tag `11ce309`.
- Docker Nginx listens on `127.0.0.1:3333`; Docker MariaDB listens on
  `127.0.0.1:3306`. The production database is in the external volume
  `labterpadu-prod-db-20261020`.
- PM2 remains enabled as an application fallback on `127.0.0.1:3335`. It was
  checked against the same MariaDB database and production storage used by
  Docker. A fallback still depends on Docker/MariaDB and is not database HA.
- Host MariaDB is disabled, not removed. Its files and the previous Docker
  volumes are retained for recovery; do not start host MariaDB while Docker
  owns port 3306.
- Laravel's persistent `storage/app` is still the production directory at
  `/home/fadil/LabterpaduFEB/storage/app`; it is bind-mounted into the app.
- The backend Compose network is currently internal. Keep it that way unless
  a reviewed application feature requires outbound network access. The current
  application source has no runtime HTTP, SMTP, or notification integration
  calls; recheck this before adding one.
- Cloudflared is host-managed with a remotely managed tunnel configuration,
  not a route file in this repository. The public site and the local Docker
  origin were both checked after cutover. Verify the public URL after any
  origin or port change; do not infer or silently change the tunnel route.
- The 2026-10-20 Proxmox VM backup was restored to an isolated, no-network test
  VM. The clone booted, all three Docker services became healthy, Laravel
  migrations were present, and home/login plus CSS/JS assets responded. The
  test clone was then removed. The backup is on the Proxmox node's local
  storage, so it protects against guest-level deployment failures but is not
  an off-node disaster-recovery copy.

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
  `3334` for rehearsal. The current production value is `3333`; PM2's fallback
  uses `3335`.
- PHP, Node, Composer, Nginx, and MariaDB base images are pinned by immutable
  registry digests; Composer and npm dependencies use their lockfiles.
- `LABTERPADU_BACKEND_INTERNAL=true` isolates PHP and migration containers
  from outbound network access. Nginx and MariaDB each have a separate
  non-internal network so Docker can publish their loopback-only host ports.
  Production currently retains this isolation; change it only after reviewing
  a specific integration requirement.

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
   `127.0.0.1:3334`). In production, Docker MariaDB uses `127.0.0.1:3306` only
   after host MariaDB stops; this keeps the PM2 fallback connected to the same
   Docker database. Confirm only the backend network is internal and that
   Nginx/DB are attached to their separate port-publishing networks. Do not
   start `app`, `migrate`, or `web` before this check.
4. Build the images, then start the rehearsal web service. Compose waits for the
   database health check, runs migrations against the selected Docker database,
   and only then starts PHP-FPM and Nginx.
5. Verify `/status`, the home page, CSS/JS assets, login, admin pages, document
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
curl -fsS http://127.0.0.1:3334/status
```

The database is not exposed beyond loopback, and Nginx is not exposed beyond
loopback. Cloudflared is managed separately from Compose; changing a published
port does not update the remotely managed tunnel route.

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
- Confirm the actual Cloudflared/Apache origin chain before changing a port.
  The current production layout keeps Docker Nginx on `3333` and PM2 on `3335`;
  do not move either listener without updating and verifying the external
  routing path. For app rollback, route traffic to PM2 while keeping it on the
  same Docker database and production storage.
- RAM and disk headroom remain acceptable with all containers running.

Keep the host MariaDB files and all old Docker volumes intact. Rollback to PM2
is only an application rollback: PM2 must continue using the Docker DB so that
writes made after cutover are not lost. Do not point PM2 back to the old host
MariaDB unless a separate data-reconciliation procedure is approved. Maintain
an additional off-node backup before treating this deployment as protected
against Proxmox host or storage failure.
