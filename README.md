# LabDigitalFEB

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Sistem Informasi Laboratorium Digital Fakultas Ekonomi dan Bisnis

## About This Project

This is a Laravel-based web application for managing the integrated laboratory of the Faculty of Economics and Business.

## Requirements

-   PHP >= 8.1
-   Composer
-   Node.js & NPM
-   MySQL/PostgreSQL

## Installation

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan serve
npm run dev
```

## Testing

Run the fast PHPUnit suite with:

```bash
composer test
```

The fast suite uses an in-memory SQLite database and temporary storage. Other useful checks:

```bash
composer test:quality   # PHPUnit, Larastan, and Pint
composer test:coverage  # PHPUnit with app line coverage (PCOV or Xdebug required)
composer test:dusk      # Browser and MySQL integration tests
composer test:all       # Fast suite, quality checks, frontend build, and Dusk
```

Dusk requires Chrome/Chromium and a dedicated local MySQL database named `labterpadu_dusk_test`. Its guard rejects other database targets; never point it at development or production data. Setup details and safety guarantees are in [docs/testing.md](docs/testing.md).

## Production deployment (PM2)

The production server runs Laravel's built-in PHP server under PM2 on port `3333`. Deploy from the project directory as the application user:

```bash
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
pm2 startOrReload ecosystem.config.json --update-env
pm2 save
pm2 status
```

Run the commands in order and confirm the app is healthy before considering the deployment complete. `ecosystem.config.json` is the source of truth for the PM2 process; it runs `artisan serve` with `--no-reload` so Laravel environment values reach PHP's server worker.

### Common deployment issues

- **Changed `.env`:** PM2 may retain the old process environment. Restart/reload with `--update-env` as shown above. Laravel's cached configuration also takes precedence over changed `.env` values: run `php artisan config:clear`, then `php artisan config:cache` to rebuild it. Do this after reviewing the production `.env`; never commit or replace production secrets.
- **Database settings seem unchanged:** `config:cache` freezes configuration, including database settings. Clear and rebuild the cache after `.env` changes. Run artisan commands only when the app is pointed at the intended database.
- **App key or credentials missing in requests:** Keep the PM2 command's `--no-reload` option. It is required for this deployment's PHP built-in server environment handling.
- **PM2 still shows an old process / app unavailable:** From the project root run `pm2 startOrReload ecosystem.config.json --update-env`, then inspect `pm2 status` and `pm2 logs labterpadu --lines 100`. Check the configured port (`3333`), permissions, and server logs before retrying.
- **Assets look stale or missing:** Re-run `npm ci && npm run build`; production serves the generated `public/build` assets, not the Vite development server.
- **Migration failed:** Check the error and database target before retrying. Do not use `migrate:fresh` or restore a dump as a deployment fix; those can destroy production data.

`pm2 save` records the current process list for reboot restoration (assuming PM2 startup integration has already been configured on the server). Back up the production database before risky schema changes; see the deployment/operations notes in `AGENTS.md` for backup and restore commands.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
