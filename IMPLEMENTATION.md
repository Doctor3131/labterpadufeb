# LabTerpaduFEB

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Sistem Informasi Laboratorium Terpadu Fakultas Ekonomi dan Bisnis - Aplikasi web untuk manajemen laboratorium dengan desain minimalis dan header kuning.

## 🎯 Features Implemented

### Functional Requirements (FR)

✅ **FR-01 Authentication**: Login system untuk Mahasiswa & Admin menggunakan email dan password  
✅ **FR-02 Landing Page**: Halaman publik dengan informasi umum tentang Lab  
✅ **FR-03 Master Data**: Database structure untuk Users dan Labs  
✅ **FR-04 Access Control**: Protected dashboard dengan middleware auth

### Non-Functional Requirements (NFR)

✅ **NFR-01 User Interface**: Desain minimalis dengan header kuning  
✅ **NFR-02 Responsiveness**: Mobile-responsive design menggunakan Tailwind CSS  
✅ **NFR-03 Performance**: Optimized dengan Vite untuk fast loading

## 📋 Requirements

-   PHP >= 8.1
-   Composer
-   Node.js & NPM
-   MySQL/PostgreSQL/SQLite

## 🚀 Installation

```bash
# Clone repository
git clone https://github.com/Firzii/LabTerpaduFEB.git
cd LabTerpaduFEB

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database (update .env dengan database credentials)
# Kemudian run migrations dan seeders
php artisan migrate:fresh --seed

# Start development
php artisan serve        # Terminal 1
npm run dev             # Terminal 2
```

Akses aplikasi di: **http://localhost:8000**

## 👥 Default Users

Setelah menjalankan seeder, tersedia akun berikut:

**Admin:**

-   Email: `admin@feb.ac.id`
-   Password: `password`

**Mahasiswa:**

-   Email: `mahasiswa@feb.ac.id`
-   Password: `password`

## 📁 Database Structure

### Users Table

-   `id` - Primary key
-   `name` - Nama lengkap
-   `email` - Email (unique)
-   `password` - Password (hashed)
-   `role` - Enum: 'admin' atau 'mahasiswa'
-   `timestamps`

### Labs Table

-   `id` - Primary key
-   `name` - Nama ruangan
-   `code` - Kode ruangan (unique)
-   `description` - Deskripsi
-   `location` - Lokasi
-   `capacity` - Kapasitas (integer)
-   `status` - Enum: 'available', 'occupied', 'maintenance'
-   `image` - Path gambar (nullable)
-   `timestamps`

## 🎨 Design System

-   **Primary Color**: Yellow (#EAB308)
-   **Style**: Minimalist with clean lines
-   **Typography**: Sans-serif font stack
-   **Components**: Rounded corners, subtle shadows
-   **Responsive**: Mobile-first approach

## 📱 Routes

### Public Routes

-   `GET /` - Landing page
-   `GET /login` - Login page
-   `POST /login` - Login handler
-   `GET /register` - Register page
-   `POST /register` - Register handler

### Protected Routes (Auth Required)

-   `GET /dashboard` - Dashboard (role-based)
-   `POST /logout` - Logout handler

## 🔧 Tech Stack

-   **Backend**: Laravel 10
-   **Frontend**: Blade Templates + Tailwind CSS 4
-   **Build Tool**: Vite 7
-   **Database**: MySQL/PostgreSQL
-   **Authentication**: Laravel Auth

## 📊 Project Structure

```
app/
├── Http/Controllers/
│   ├── AuthController.php         # Authentication logic
│   ├── DashboardController.php    # Dashboard logic
│   └── LandingController.php      # Landing page logic
├── Models/
│   ├── User.php                   # User model with roles
│   └── Lab.php                    # Lab model
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php         # Main layout
│   ├── auth/
│   │   ├── login.blade.php       # Login page
│   │   └── register.blade.php    # Register page
│   ├── dashboard/
│   │   ├── admin.blade.php       # Admin dashboard
│   │   └── mahasiswa.blade.php   # Mahasiswa dashboard
│   └── landing.blade.php          # Public landing page
routes/
└── web.php                        # Route definitions
database/
├── migrations/                    # Database migrations
└── seeders/
    └── DatabaseSeeder.php        # Sample data
```

## 🎯 Next Steps

Untuk pengembangan selanjutnya:

-   CRUD management untuk Labs (Admin)
-   Booking system untuk ruangan
-   User management (Admin)
-   Report dan statistik
-   Notifikasi email
-   Upload gambar lab

## 📝 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
