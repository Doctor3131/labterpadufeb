# AGENTS.md

Guidance for working in **LabDigitalFEB**, a Laravel 12 app managing the FEB integrated lab: booking, inventory, asset borrowing, and data-service requests (BPS, Refinitiv, Bloomberg).

## Commands

```bash
# Dev
composer install && npm install
composer run dev                  # php serve + queue + pail + vite (concurrent)
composer run test                 # config:clear then php artisan test (sqlite :memory:)
php artisan test --filter=TestClassName
./vendor/bin/pint                 # PHP lint/format (do not run --fix unless intended)
php artisan import:mahasiswa      # reads mahasiswa_feb.csv from project root; {file?} arg overrides
php artisan bps:sync-catalog [--dry-run] [--keep-missing]
php artisan items:update-categories [--force]
npm run build

# Server lifecycle (PM2, port 3333)
pm2 startOrReload ecosystem.config.json --update-env
pm2 status / logs / restart labterpadu / stop labterpadu

# Ops (manual — no scripts)
mysqldump -u labterpadu -p labterpadu | gzip > backups/backup_labterpadu_$(date +%Y%m%d-%H%M%S).sql.gz
gunzip -c backups/<dump>.sql.gz | mysql -u labterpadu -p labterpadu   # DESTRUCTIVE overwrite
php artisan migrate / fresh / seed
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

## Testing
PHPUnit 11 (`tests/TestCase.php`), **not** Pest despite the pest allow-entry in composer.json. phpunit.xml runs sqlite `:memory:`, sync queue, array cache/session. Only example scaffolding exists — no established suite yet.

## Deployment

Bare-metal server. PHP's built-in server is managed by **PM2** (`ecosystem.config.json`) on port 3333; no Docker, no shell scripts. Nothing dispatches jobs, so there is no queue worker — `artisan serve` with `--no-reload` (carries the env-filtering fix so APP_KEY/DB creds reach the `php -S` worker) is all PM2 runs.

```bash
cd /path/to/repo
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
pm2 startOrReload ecosystem.config.json --update-env
```

- **Seeding:** `db:seed` runs only when `bookings` is empty (same guard that used to live in the Docker entrypoint) — run it manually on a fresh DB so dump-restored data stays authoritative. Similarly, `import:mahasiswa` should only run when `mahasiswa_feb` is empty.
- **Backup / restore:** plain `mysqldump` / `mysql` against MySQL (see Commands). `backups/` holds the gzip dumps; add the mysqldump line to cron. Restoring a dump is a destructive overwrite of the current DB.
- **`mahasiswa_feb.csv`** ships out-of-band to the server (student PII — never in the repo). On a fresh server, copy it into the project root and run `php artisan import:mahasiswa` once.

## Gotchas
- **README.md has stale planning notes appended below the License section** (in Indonesian, leftover feature-planning scratch). Trust AGENTS.md/CLAUDE.md over README prose.
- `backups/` and `labterpadu-*.sql` dumps live in the repo root — never commit fresh dumps or treat them as source of truth. (`backups/` may already contain old `backup_labterpadu_*.sql` files from an earlier scheme — ignore/rotate them, don't commit.)
- **`mahasiswa_feb.csv` is not in the repo** (purged from history; never re-add). For local dev, regenerate it from the DB (`SELECT nim,nama,prodi INTO OUTFILE ...`) or restore it from a DB backup.
- **`config:cache` freezes DB settings.** On the server the app runs with `config:cache`; ad-hoc artisan commands against another database silently hit the cached config. Run `php artisan config:clear` first (or re-cache after).
- **`migrate --force` is idempotent** ("Nothing to migrate" on redeploy). The old "restart fragility" crash (`2026_02_03_215614`) was a stale-dump artifact: `labterpadu-10-05-2026.sql` predates the `2026_08_04/08_05` migrations, so dump-import + boot hit it. Check a dump's migration timestamp against the repo's before importing.
- **MySQL `root` is `root@localhost` only** — TCP root auth is denied; the app connects as `labterpadu@'%'` (granted only on `labterpadu`). Creating a scratch DB requires `GRANT ALL ON <db>.* TO 'labterpadu'@'%'`.
- **PHP 8.5 prints PDO deprecations to stdout** — when capturing `php artisan key:generate --show` output, grep for the `base64:` line to avoid deprecation noise.
- **The Bash tool shell is zsh**, which does not word-split variables (`$VAR cmd` fails). Write multi-command test scripts as `bash file.sh`.
