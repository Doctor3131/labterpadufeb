# LabTerpaduFEB

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Sistem Informasi Laboratorium Terpadu Fakultas Ekonomi dan Bisnis

## About This Project

This is a Laravel-based web application for managing the integrated laboratory of the Faculty of Economics and Business.

## Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/PostgreSQL

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

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
