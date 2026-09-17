# LabDigitalFEB

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Sistem Informasi Laboratorium Digital Fakultas Ekonomi dan Bisnis

## About This Project

This is a Laravel-based web application for managing the integrated laboratory of the Faculty of Economics and Business.

## Requirements

-   PHP >= 8.2 (lockfile saat ini butuh >= 8.4; image Docker memakai PHP 8.4)
-   Composer
-   Node.js & NPM
-   MySQL 8.0 (production)

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

## Docker

Production-style setup: `app` (Laravel 12, `artisan serve` port 3333, non-root, read-only FS)
+ `db` (MySQL 8.0, named volume) + `proxy` (Nginx port 80; TLS di-terminate di Cloudflare).

```bash
# 1. Wajib di .env: DB_PASSWORD, MYSQL_ROOT_PASSWORD,
#    APP_KEY (php artisan key:generate --show | grep base64:), APP_URL
# 2. Kepemilikan folder upload (di-bind ke container, bukan bagian image)
sudo chown -R 10000:10000 ./data
# 3. Jalan
docker compose up -d --build
curl http://localhost/up   # 200 = sehat (nginx -> app -> DB)
```

Catatan:

- Entrypoint menunggu DB healthy lalu `migrate --force` (hanya migration pending,
  idempotent — tidak pernah `fresh`). Tanpa seed otomatis.
- File upload user hidup di `./data/{uploads,private}` di host; image steril dari data user.
- `CACHE_STORE`/`SESSION_DRIVER`/`QUEUE_CONNECTION` dipaksa `file/sync` via compose.
- Gagal-boot cepat bila `APP_KEY` invalid atau bind-mount tidak writable (lihat log `entrypoint:`).

Dev dengan hot reload (Compose Watch, DB yang sama):

```bash
docker compose -f compose.yaml -f compose.override.yaml up --watch --build
```

Restore dump DB (manual, tanpa auto-seed):

```bash
docker compose exec -T db mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_DATABASE" < backup.sql
docker compose exec app php artisan migrate --force
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


- Di resources/views/booking/create.blade.php (section #non-perkuliahan-fields):
  - Pindahkan card checkbox Bimbingan bersama Dosen? ke posisi paling atas di dalam blok non-perkuliahan.
  - Tambah checkbox baru:
    - label: Peminjaman atas nama dosen
    - id/name rekomendasi: is_on_behalf_lecturer (boolean request field)
  - Saat checkbox is_on_behalf_lecturer dicentang:
    - field NIM disembunyikan + tidak required
    - field NIP ditampilkan + required
    - label/hint menyesuaikan (supaya user tidak bingung)
  - Saat tidak dicentang:
    - kembali ke mode default non-perkuliahan (NIM required, NIP optional/hidden)
- Di JS form booking (create.blade.php script):
  - Tambah setupOnBehalfLecturerToggle() mirip pola setupBimbinganDosenToggle().
  - Update validateStep2() khusus selectedBookingType === 'non_perkuliahan':
    - jika atas nama dosen: validasi NIP 18 digit
    - jika tidak: validasi NIM 14 digit
  - Update summary generator agar menampilkan NIP ketika mode atas nama dosen aktif.
- Di app/Http/Controllers/BookingController.php:
  - Tambah validasi request boolean:
    - is_on_behalf_lecturer => nullable|boolean
  - Ubah rule non-pribadi yang saat ini selalu mewajibkan nim:
    - untuk booking_type=non_perkuliahan + is_on_behalf_lecturer=1 => nip required, nim nullable
    - selain itu => nim required (behavior lama tetap)
  - (opsional aman) set applicant_status = 'Dosen' otomatis saat mode atas nama dosen aktif untuk konsistensi data.
- Data/model:
  - Tidak wajib migration baru jika checkbox ini hanya untuk alur validasi input (transient request flag).
  - Data yang disimpan tetap pakai kolom existing nim / nip.
Catatan penting:
- Kalau backend tidak diubah, form akan tetap gagal karena saat ini rule non-pribadi masih hard-required nim.
Satu konfirmasi kecil sebelum implement:
- Saat checkbox Peminjaman atas nama dosen aktif, kamu mau saya otomatis set applicant_status menjadi Dosen di backend (recommended), atau cukup swap NIM→NIP tanpa menyentuh applicant_status?
▣  Plan · gpt-5.3-codex · 1m 6s
iya ganti juga. apakah akan merubah db? 
