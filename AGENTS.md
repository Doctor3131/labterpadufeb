# AGENTS.md

Guidance for working in **LabDigitalFEB**, a Laravel 12 app managing the FEB integrated lab: booking, inventory, asset borrowing, and data-service requests (BPS, Refinitiv, Bloomberg).

## Commands

```bash
# Native (non-docker) dev
composer install && npm install
composer run dev                  # php serve + queue + pail + vite (concurrent)
composer run test                 # config:clear then php artisan test (sqlite :memory:)
php artisan test --filter=TestClassName
./vendor/bin/pint                 # PHP lint/format (do not run --fix unless intended)
php artisan import:mahasiswa      # reads mahasiswa_feb.csv from project root; {file?} arg overrides
php artisan bps:sync-catalog [--dry-run] [--keep-missing]
php artisan items:update-categories [--force]
npm run build

# Docker (compose) — preferred workflow
make env                          # create .env.docker from example (fails if it exists)
make up-d MODE=dev                # background; MODE=prod is default
make logs / ps / down
make clean                        # down -v — destructive, wipes DB volume
make app / tinker / artisan CMD=route:list / db-shell
make migrate / fresh / seed
make import FILE=labterpadu-10-05-2026.sql   # restore a SQL dump into the db container
./db-import.sh <file.sql> [--force]          # standalone import (interactive creds)
```

## Architecture

### Access tiers (route middleware aliases in `bootstrap/app.php`)
1. **Public** (no auth) — booking form, BPS/Refinitiv/Bloomberg requests, personal-borrowing NIM validation, feedback. All POSTs are `throttle`-limited.
2. **Admin** (`auth` + `admin`) — everything in `routes/web.php` under the admin group: inventory, schedules, reports, labs, all borrowing workflows. `isAdmin()` is true for both `admin` and `super_admin`.
3. **Super Admin** (`auth` + `super_admin`) — user management only.

`User::role` is a plain string column: `'admin'` | `'super_admin'`.

### Controller split by audience
Top-level `App\Http\Controllers\*` = **public** flows (e.g. `BloombergRequestController`). `App\Http\Controllers\Admin\*` = **admin management** of the same module (e.g. `Admin\BloombergRequestController`). Match the audience when adding features; don't overload one controller.

### Domain modules
- **Booking** — types via `Booking::BOOKING_TYPES`: `perkuliahan_tetap` / `perkuliahan_tidak_tetap` (class-based), `non_perkuliahan` (flags `is_bimbingan_dosen`, `is_on_behalf_lecturer`), `pribadi` (`pribadi_sub_type`: mahasiswa/non_mahasiswa). Each submission gets a `tracking_token` UUID for status lookup without login. Conflict detection (`Lab::isAvailable()`) checks both recurring `schedules` and one-off `bookings`.
- **Schedule** — recurring lab timetable, created from approved bookings (`schedule()` relation). Deleting one cleans up `ScheduleDocument` files.
- **Inventory** — `InventoryService` (all writes in `DB::transaction()`). Three `TrackingModeEnum` modes: `STRUCTURED_TAG` (tag format `{proc_source}.{arrival_mmyy}.{type_code}.{lab_code}.{seq}`), `SEAT_NUMBER`, `AGGREGATE` (bulk count, no unit ids). Conditions: `ConditionEnum` BAIK/RUSAK/HILANG/MAINTENANCE. Ledger via `TransactionTypeEnum` RECEIPT/CONDITION_CHANGE/TRANSFER/ADJUSTMENT.
- **Asset Borrowing** — `BorrowingDocumentService` generates a "Surat Peminjaman Barang" PDF. Doc number `XXX/SPB/UPKFEB/ROMAN_MONTH/YEAR`. States: pending → approved/rejected → handed out → received (with damage/replacement tracking).
- **Data services** — BPS (`BpsRequest`, `BpsMasterData`, `BpsSubData`; catalog synced via `bps:sync-catalog`), Refinitiv (`RefinitivRequest`; hadir/tidak-hadir attendance), Bloomberg (`BloombergRequest`, `BlockedDate`; capacity + blocked-date calendar).
- **Personal Borrowing** — NIM validated against `mahasiswa_feb` table (populated by `import:mahasiswa`).

### Frontend
Blade + Tailwind CSS 4. No JS framework — vanilla JS for interactive forms (multi-step booking, AJAX lab availability). Base layout `resources/views/layouts/app.blade.php`. PDFs via `barryvdh/laravel-dompdf` in `resources/views/pdf/`.

### Key constants & helpers
- `DayHelper::fromEnglish()` — English day → Indonesian (used in schedule conflict checks).
- `CategoryEnum` — hardcoded asset categories (PC, Monitor, Laptop, ...).
- `Booking::BOOKING_TYPES`, `Booking::ACTIVITY_TYPES`, `Booking::APPLICANT_STATUSES` — source of truth shared between validation rules and blade forms; always use these constants, never duplicate.

### Reports & exports
Excel uses `phpoffice/phpspreadsheet` directly (no Laravel-Excel wrapper). Reports live in `Admin\ReportController` (also exports Word).

## Docker stack (compose)

- Config lives in `.env.docker` (gitignored; `make env` copies `.env.docker.example`). All DB/session/queue/cache settings flow from it; the `db` service creates DB+user from `DB_*` and fails fast if vars are missing. Defaults: `DB_DATABASE/DB_USERNAME/DB_PASSWORD=labterpadu`, `DB_ROOT_PASSWORD=root`.
- Two compose files: `docker-compose.yml` (prod) + `docker-compose.dev.yml` overlay. Dev (`MODE=dev`) bind-mounts source, adds a Vite HMR service on `5173`, forces `APP_ENV=local`. Prod bakes source/assets (`Dockerfile` target `prod`), no bind mounts. Ports: app `3333`, MySQL `127.0.0.1:3307`.
- **Boot order** (`docker/entrypoint.sh`): composer deps (dev) → APP_KEY → `storage:link` → `migrate --force` → **seed guard** → prod `config:cache`/`route:cache` → **mahasiswa import guard** → supervisord (`php artisan serve --host=0.0.0.0 --port=3333 --no-reload` + `queue:work`). Session/queue/cache are **database-backed** — no Redis.
- **Seed guard:** `db:seed` runs only when `bookings` is empty; prod `import:mahasiswa` runs only when `mahasiswa_feb` is empty. This keeps dump-restored data authoritative across container restarts — the DatabaseSeeder would otherwise overwrite it.
- **Restoring a dump:** `make import FILE=<dump>.sql` → `db-import.sh`. Interactive credential prompts (host/user/db/password), a destructive-import confirmation (`--force` bypasses), dump piped to the `db` container via **stdin** (no temp file), creds passed as `-e` env (never argv). Non-interactive/CI: set `DB_USER/DB_PASS/DB_NAME/DB_HOST` and `--force`.

## Testing
PHPUnit 11 (`tests/TestCase.php`), **not** Pest despite the pest allow-entry in composer.json. phpunit.xml runs sqlite `:memory:`, sync queue, array cache/session. Only example scaffolding exists — no established suite yet.

## Deployment
Deployment is the **Docker compose stack** (prod target). `rsync-deploy.sh` + `ecosystem.config.json` (PM2, `artisan serve` on 3333) remain in the tree as a legacy bare-metal path but are not the active deployment.

## Gotchas
- **README.md has stale planning notes appended below the License section** (in Indonesian, leftover feature-planning scratch). Trust AGENTS.md/CLAUDE.md over README prose.
- `backups/` and `labterpadu-*.sql` dumps live in the repo root — never commit fresh dumps or treat them as source of truth.
- **`config:cache` ignores `-e DB_*` overrides.** The prod app caches config at boot, so `docker compose exec -e DB_DATABASE=... app artisan migrate` silently hits the cached DB. Run `php artisan config:clear` first for ad-hoc runs against other databases.
- **Restart fragility (pre-existing):** the entrypoint re-runs `migrate --force` on every boot; it currently re-runs all migrations and fails at `2026_02_03_215614` with a `renderThrowable()` crash (no durable change). The `labterpadu-10-05-2026.sql` dump predates current migrations (`2026_08_04/08_05`), so dump-import + boot will hit this too.
- **MySQL `root` is `root@localhost` only** — TCP root auth is denied; the app connects as `labterpadu@'%'` (granted only on `labterpadu`). Creating a scratch DB requires `GRANT ALL ON <db>.* TO 'labterpadu'@'%'`.
- **`docker compose run` hangs** (entrypoint `exec`s supervisord, which never exits). For one-off boot checks use `docker run -d` + `docker logs` + `docker rm`.
- **PHP 8.5 prints PDO deprecations to stdout** (entrypoint filters them when generating APP_KEY).
- **The Bash tool shell is zsh**, which does not word-split variables (`$VAR cmd` fails). Write multi-command docker tests as `bash file.sh`.
