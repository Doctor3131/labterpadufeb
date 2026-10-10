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

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
