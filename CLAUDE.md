# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**LabDigitalFEB** is a Laravel 12 web application for managing the integrated laboratory of the Faculty of Economics and Business (FEB). It handles lab booking, inventory management, asset borrowing, and external data service requests (BPS, Refinitiv, Bloomberg).

## Commands

```bash
# Install all dependencies
composer install && npm install

# Full dev environment (PHP server + queue + logs + Vite HMR, all concurrent)
composer run dev

# Run tests
composer run test

# Run a single test
php artisan test --filter=TestClassName

# Lint PHP code
./vendor/bin/pint

# Database migrations
php artisan migrate

# Import student data from CSV (mahasiswa_feb.csv in project root)
php artisan import:mahasiswa

# Sync BPS catalog data
php artisan bps:sync-catalog [--dry-run] [--keep-missing]

# Build frontend assets
npm run build

# Deploy via PM2 (see Deployment)
pm2 startOrReload ecosystem.config.json --update-env
```

Vite is configured to auto-detect local IP so HMR works from mobile devices on the same network.

## Architecture

### Request Flow & Auth Layers

Routes are grouped into three access tiers:

1. **Public** (no auth) — booking form, BPS/Refinitiv/Bloomberg requests, personal borrowing NIM validation, feedback. Rate-limited with `throttle` middleware.
2. **Admin** (`auth` + `EnsureAdmin`) — inventory, schedules, reports, lab management, all borrowing workflows. The `isAdmin()` check passes for both `admin` and `super_admin` roles.
3. **Super Admin** (`auth` + `EnsureSuperAdmin`) — user management only.

`User::role` is a plain string column: `'admin'` | `'super_admin'`.

### Controller Namespacing

Controllers are split by audience. Top-level `App\Http\Controllers\*` handle **public-facing** flows (booking, BPS/Refinitiv/Bloomberg request submission, landing, feedback, personal borrowing). `App\Http\Controllers\Admin\*` handle the **admin CRUD/management** side of the same modules (e.g. there is both a public `BloombergRequestController` and an `Admin\BloombergRequestController`). When adding a feature, match the audience to the correct namespace rather than overloading one controller.

### Domain Modules

**Booking** (`Booking` model, `BookingController`): Four booking types defined in `Booking::BOOKING_TYPES`:
- `perkuliahan_tetap` / `perkuliahan_tidak_tetap` — class-based, requires course/lecturer info
- `non_perkuliahan` — events/seminars; supports `is_bimbingan_dosen` and `is_on_behalf_lecturer` flags
- `pribadi` — personal use with `pribadi_sub_type` (mahasiswa/non_mahasiswa)

Each submission gets a `tracking_token` (UUID) that users can use to check booking status without logging in. Conflict detection (`Lab::isAvailable()`) checks both recurring `schedules` and one-off `bookings` tables.

**Schedule** (`Schedule` model): Recurring lab timetable. Created from approved bookings (`schedule()` relation on Booking). Linked back via `booking_id`. Deleting a schedule also cleans up associated `ScheduleDocument` files from storage.

**Inventory** (`InventoryService`): Three asset tracking modes (`TrackingModeEnum`):
- `STRUCTURED_TAG` — individual units with tags in format `{proc_source}.{arrival_mmyy}.{type_code}.{lab_code}.{seq}` (PCs, laptops, TVs)
- `SEAT_NUMBER` — individual units tracked by seat position (mice, keyboards)
- `AGGREGATE` — bulk count tracking without unit identifiers (routers, ACs)

Asset conditions: `BAIK`, `RUSAK`, `HILANG`, `MAINTENANCE` (`ConditionEnum`). Inventory transactions are typed (`TransactionTypeEnum`): `RECEIPT`, `CONDITION_CHANGE`, `TRANSFER`, `ADJUSTMENT`.

**Asset Borrowing** (`AssetBorrowing`, `BorrowingDocumentService`): Formal item lending that generates a PDF "Surat Peminjaman Barang". Document number format: `XXX/SPB/UPKFEB/ROMAN_MONTH/YEAR`. Workflow states: pending → approved/rejected → handed out → received back (with optional damage/replacement tracking).

**Data Services**:
- BPS (`BpsRequest`, `BpsMasterData`, `BpsSubData`) — request access to BPS statistical data catalog. Catalog synced via `php artisan bps:sync-catalog`.
- Refinitiv (`RefinitivRequest`) — terminal access requests with hadir/tidak-hadir attendance tracking.
- Bloomberg (`BloombergRequest`, `BlockedDate`) — terminal reservation system with capacity management and blocked-date calendar.

**Personal Borrowing** (`PersonalBorrowingController`): Lighter-weight asset lending. NIM is validated against the `mahasiswa_feb` table (populated by `import:mahasiswa`).

### Frontend

Blade templates with Tailwind CSS 4. No JS framework — vanilla JS for interactive form sections (multi-step booking form, AJAX lab availability checks). Layout base: `resources/views/layouts/app.blade.php`. PDF generation uses `barryvdh/laravel-dompdf` with templates in `resources/views/pdf/`.

### Services Layer

- `InventoryService` — structured tag generation, inventory add/transfer operations, all inside `DB::transaction()`
- `BorrowingDocumentService` — PDF generation and `document_number` formatting for asset borrowings
- `ScheduleService` — schedule creation/conflict logic

### Key Helpers & Enums

- `DayHelper::fromEnglish()` — converts English day names to Indonesian (used for schedule conflict checks)
- `CategoryEnum` — hardcoded asset categories (PC, Monitor, Laptop, etc.)
- `Booking::BOOKING_TYPES`, `Booking::ACTIVITY_TYPES`, `Booking::APPLICANT_STATUSES` — used in both validation rules and blade form rendering; always use these constants to avoid duplication

### Reports & Exports

Excel exports use `phpoffice/phpspreadsheet` directly (no Laravel-Excel wrapper). Report generation lives in `Admin\ReportController`.

### Testing

Tests run on PHPUnit 11 (configured in `tests/TestCase.php`), not Pest — despite the `pestphp/pest-plugin` allow-entry in `composer.json`. Only example scaffolding exists in `tests/Feature` and `tests/Unit`; there is no established test suite yet. `composer run test` clears config before running.

### Deployment

Production runs PHP's built-in server managed by PM2 (`ecosystem.config.json`) on port 3333 — no Docker, no shell scripts. Deploy by pulling the repo, running `composer install --no-dev`, `npm ci && npm run build`, `php artisan migrate --force`, then `pm2 startOrReload ecosystem.config.json --update-env`. The PM2 app runs `php artisan serve --no-reload` so APP_KEY/DB creds reach the `php -S` worker.
