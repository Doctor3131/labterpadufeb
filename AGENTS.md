# AGENTS.md

Guidance for working in **LabDigitalFEB**, a Laravel 12 app managing the FEB integrated lab: booking, inventory, asset borrowing, and data-service requests (BPS, Refinitiv, Bloomberg).

## Commands

```bash
composer install && npm install   # install everything
composer run dev                  # full dev env: php serve + queue + pail logs + vite HMR (concurrent)
composer run test                 # config:clear then php artisan test (sqlite :memory:)
php artisan test --filter=TestClassName   # single test
./vendor/bin/pint                 # PHP lint/format (do not run --fix unless intended)
php artisan migrate
php artisan import:mahasiswa      # reads mahasiswa_feb.csv from project root; {file?} arg to override
php artisan bps:sync-catalog [--dry-run] [--keep-missing]
php artisan items:update-categories [--force]
npm run build
./rsync-deploy.sh <user@host> <remote_path> [ssh_port]   # set POST_DEPLOY_CMD for post-deploy steps
```

Vite auto-detects the local IP and binds `0.0.0.0:5173` (strict port) so HMR works from phones on the LAN.

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
Production runs PHP's built-in server via PM2 (`ecosystem.config.json`) on port 3333. `rsync-deploy.sh` syncs the tree (excludes vendor/, node_modules/, .env, sql/log/backup files, public/build); set `POST_DEPLOY_CMD` env (e.g. `php artisan optimize`) for post-deploy steps.

## Gotchas
- **README.md has stale planning notes appended below the License section** (in Indonesian, leftover feature-planning scratch from a past session). Trust this file / CLAUDE.md over README prose.
- `backups/` and `labterpadu-*.sql` DB dumps live in the repo root — never commit fresh dumps or treat them as source of truth.
