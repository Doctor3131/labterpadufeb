# Automated tests

## CLI commands

| Command | Checks |
| --- | --- |
| `composer test` | Fast PHPUnit suite on in-memory SQLite |
| `composer test:coverage` | Fast suite with app line coverage and a 53.35% minimum ratchet; requires Xdebug or PCOV |
| `composer test:quality` | Fast suite, Larastan, then Pint check-only for test code |
| `composer test:dusk` | Builds frontend assets, starts the local app, then runs Dusk in Chrome against MySQL |
| `composer test:all` | Runs the fast suite, Larastan, Pint, then Dusk sequentially |

`test:all` requires the Dusk database and Chrome setup below. It deliberately does not provision either one. The fast-suite wrapper forces `APP_ENV=testing`, SQLite `:memory:`, and a temporary Laravel storage root even if the developer's `.env` points to populated MySQL or uploaded files; its temporary storage is removed on exit. HTTP client calls are blocked unless a test explicitly fakes them. Browser tests block external host resolution and use local assets.

Larastan analyzes `app/` at level 5. `phpstan-baseline.neon` records existing diagnostics so new ones fail the check; review any baseline updates rather than regenerating it automatically. Pint runs in check-only mode for `tests/`: app-wide Pint currently reports legacy formatting across production files, which this test-only phase intentionally does not reformat. Fast PHPUnit uses `phpunit.xml` with forced SQLite settings; Dusk uses a separate `phpunit.dusk.xml` so those overrides cannot redirect browser tests.

`composer test:coverage` measures line coverage for `app/` from the fast PHPUnit suite and enforces a 53.35% minimum, above the original 28.3% baseline. The most recent accepted run measured 53.35% (3,529/6,615 lines). The floor is updated only after reviewing the suite and accepting a higher measured result. The runner uses a separate temporary storage root and prints the coverage summary even when tests fail; the overall command still exits unsuccessfully when tests fail. Regression tests remain active rather than being excluded to produce a green report. Enable PCOV or Xdebug in the CLI PHP runtime before running it.

## Dusk browser suite

Dusk uses a **separate local MySQL database** named `labterpadu_dusk_test`. The database must be empty before a run. Dusk runs migrations only after a read-only check confirms it has no tables or views, then removes the schema it created afterward. Never point it at development or production data.

### Create the database and restricted user

On the local machine, open a MySQL administrative session (on this server, `sudo mysql` uses the local root socket) and run:

```sql
CREATE DATABASE labterpadu_dusk_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER 'labterpadu_test'@'127.0.0.1' IDENTIFIED BY 'replace-with-a-local-test-password';
GRANT ALL PRIVILEGES ON labterpadu_dusk_test.* TO 'labterpadu_test'@'127.0.0.1';
```

This user can only modify the dedicated Dusk database. If the database or user already exists, inspect it rather than blindly rerunning `CREATE` statements.

### Configure and run Dusk

1. Copy the example: `cp env.dusk.example .env.dusk.local`.
2. Edit `DB_USERNAME` and `DB_PASSWORD` in `.env.dusk.local` to match the local MySQL user. Keep `DB_DATABASE=labterpadu_dusk_test` and `DB_HOST=127.0.0.1`.
3. Install Chrome or Chromium. Then run `php artisan dusk:chrome-driver --detect` to install a matching ChromeDriver (Composer does not install the browser or driver).
4. Run `composer test:dusk` or the full sequential suite with `composer test:all`.

The Dusk runner checks the environment and database before starting the server. It refuses to run unless the connection is MySQL, the database name is exactly `labterpadu_dusk_test`, the database host is local, `APP_ENV=local`, and `APP_URL` is exactly `http://127.0.0.1:8001`. A read-only preflight also refuses any database that already has tables or views; this prevents migrations from deleting pre-existing data. It refuses to use port `8001` if another local process is listening there. It temporarily uses `.env.dusk.local`, starts Laravel on `127.0.0.1:8001`, runs Dusk, then stops the server and restores `.env`. It will not create the database or grant privileges.

The runner creates a fresh temporary Laravel storage root for the web server and removes that temporary directory on exit, so Dusk uploads, generated PDFs, sessions, and logs cannot touch the app's existing `storage` files. After Dusk starts, the runner cleans only the dedicated database (verified empty before the run) and verifies it has no tables or views, including when a test fails. The MySQL migration rollback regression is covered and currently passes. The latest guarded run passed all 7 browser/MySQL tests; the Dusk database was empty afterward and port 8001 was released.

`.env.dusk.local` is ignored by Git. Never put production credentials in it. The browser journeys cover public booking submission/admin approval, asset-borrowing approval/handout, and an inventory condition update.
