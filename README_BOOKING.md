# 🎓 Sistem Peminjaman Laboratorium FEB UNDIP

> **Status:** ✅ Ready for Testing  
> **Version:** 1.0.0  
> **Last Updated:** December 8, 2025

## 📋 Daftar Isi

1. [Overview](#overview)
2. [Features Implemented](#features-implemented)
3. [Quick Start](#quick-start)
4. [Technology Stack](#technology-stack)
5. [Project Structure](#project-structure)
6. [Documentation](#documentation)
7. [Testing Guide](#testing-guide)
8. [Next Steps](#next-steps)

---

## 🎯 Overview

Sistem Peminjaman Laboratorium adalah aplikasi web yang memungkinkan mahasiswa, dosen, dan pihak eksternal untuk mengajukan peminjaman laboratorium di FEB UNDIP secara online. Sistem ini mengelola jadwal perkuliahan dan booking laboratorium secara terintegrasi.

### Key Highlights

- ✅ **Public Booking Form** - Tidak perlu login untuk mengajukan peminjaman
- ✅ **Smart Lab Recommendation** - Sistem otomatis menyarankan lab berdasarkan jumlah peserta
- ✅ **Real-time Availability Check** - Cek ketersediaan lab secara real-time
- ✅ **3 Tipe Peminjaman** - Perkuliahan Tetap, Perkuliahan Tidak Tetap, Non-Perkuliahan
- ✅ **Responsive Design** - Mobile-friendly dengan Tailwind CSS
- ✅ **Document Upload** - Support upload PDF untuk verifikasi

---

## ✨ Features Implemented

### 🌐 Public Features

#### 1. Landing Page
- Tampilan jadwal mingguan (Senin-Sabtu) dengan tabs
- Sticky navbar dengan branding FEB UNDIP
- Tombol CTA "Ajukan Peminjaman"
- Hero section dengan gradient yellow theme

#### 2. Booking Form
- **Tipe Peminjaman:**
  - Perkuliahan Tetap (recurring setiap minggu)
  - Perkuliahan Tidak Tetap (one-time)
  - Non-Perkuliahan (events, seminars, dll)

- **Conditional Fields:** Form berubah dinamis sesuai tipe yang dipilih

- **Smart Lab Selection:**
  - Input jumlah peserta → sistem rekomendasikan lab yang paling pas
  - Prioritas: Lab terkecil yang bisa menampung
  - Warning jika over capacity

- **Real-time Availability:**
  - Check jadwal bentrok otomatis
  - Visual feedback (green = available, red = busy)

- **Document Upload:**
  - Perkuliahan: Scan KTM Koordinator MK
  - Non-Perkuliahan: Dokumen Pendukung
  - Format: PDF, Max 5MB

#### 3. Success Page
- Booking confirmation dengan ID
- Ringkasan detail peminjaman
- Status tracking
- CTA untuk ajukan peminjaman lagi

### 🔐 Admin Features (Future)
- Dashboard approval
- List pending/approved/rejected bookings
- Approve/reject dengan catatan
- Auto-create schedule entries
- Approval logging

---

## 🚀 Quick Start

### Prerequisites
```bash
- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL
```

### Installation

1. **Clone & Install Dependencies**
```bash
cd "D:\Lab Digital\LabDigitalFEB"
composer install
npm install
```

2. **Environment Setup**
```bash
# File .env sudah ada, pastikan database config sudah benar:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=labDigital
DB_USERNAME=root
DB_PASSWORD=
```

3. **Database Migration**
```bash
php artisan migrate:fresh --seed
```

4. **Storage Link**
```bash
php artisan storage:link
```

5. **Start Development Servers**

Terminal 1 (Laravel):
```bash
php artisan serve
```

Terminal 2 (Vite):
```bash
npm run dev
```

6. **Access Application**
```
Landing Page: http://localhost:8000
Booking Form: http://localhost:8000/booking
Login: http://localhost:8000/login
```

---

## 🛠️ Technology Stack

### Backend
- **Laravel 12.41.1** - PHP Framework
- **MySQL** - Database
- **Eloquent ORM** - Database abstraction

### Frontend
- **Blade Templates** - Server-side rendering
- **Tailwind CSS v4** - Utility-first CSS framework
- **Vanilla JavaScript** - Client-side interactions
- **Vite 7.2.6** - Build tool & HMR

### File Storage
- **Laravel Storage** - Local disk for PDF uploads
- **Symbolic Link** - Public access to storage

---

## 📁 Project Structure

```
LabDigitalFEB/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── BookingController.php       ← NEW
│   │       ├── DashboardController.php
│   │       └── LandingController.php
│   └── Models/
│       ├── Booking.php                     ← UPDATED
│       ├── Lab.php
│       ├── Schedule.php
│       └── User.php
│
├── database/
│   ├── migrations/
│   │   ├── 2025_12_04_025721_create_labs_table.php
│   │   ├── 2025_12_05_000001_create_schedules_table.php
│   │   └── 2025_12_05_000002_create_bookings_table.php  ← UPDATED
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── resources/
│   └── views/
│       ├── landing.blade.php               ← UPDATED
│       ├── booking/
│       │   ├── create.blade.php            ← NEW
│       │   └── success.blade.php           ← NEW
│       └── auth/
│
├── routes/
│   └── web.php                             ← UPDATED
│
├── storage/
│   └── app/
│       └── public/
│           └── booking-documents/          ← NEW (PDF uploads)
│
└── Documentation/
    ├── BOOKING_SYSTEM_SUMMARY.md           ← Overview sistem
    ├── BUSINESS_RULES.md                   ← Aturan bisnis
    ├── USER_FLOW.md                        ← Flow diagram
    └── API_DOCUMENTATION.md                ← API reference
```

---

## 📚 Documentation

Dokumentasi lengkap tersedia di folder project:

| File | Deskripsi |
|------|-----------|
| `BOOKING_SYSTEM_SUMMARY.md` | Overview lengkap sistem booking |
| `BUSINESS_RULES.md` | Aturan bisnis & logic aplikasi |
| `USER_FLOW.md` | Flow diagram & user journey |
| `API_DOCUMENTATION.md` | API endpoints & request/response |
| `IMPLEMENTATION.md` | Implementation notes (existing) |

---

## 🧪 Testing Guide

### Manual Testing Checklist

#### 1. Landing Page
- [ ] Akses http://localhost:8000
- [ ] Cek jadwal tabs (Senin-Sabtu) berfungsi
- [ ] Klik "Ajukan Peminjaman" → redirect ke `/booking`
- [ ] Klik "Login Asisten Lab" → redirect ke `/login`

#### 2. Booking Form - Non-Perkuliahan
- [ ] Pilih tipe "Non-Perkuliahan"
- [ ] Isi data peminjam (nama, prodi, nim, telp, alamat)
- [ ] Pilih jenis kegiatan: Seminar
- [ ] Isi nama kegiatan, jabatan, kebutuhan peralatan
- [ ] Input jumlah peserta: 25
- [ ] **Cek:** Sistem harus rekomendasikan EL. 301 (kapasitas 30)
- [ ] Pilih tanggal: Besok
- [ ] Pilih jam: 13:00 - 15:00
- [ ] **Cek:** Status availability harus ✓ Tersedia (hijau)
- [ ] Upload PDF < 5MB
- [ ] Submit → redirect ke success page

#### 3. Booking Form - Perkuliahan Tetap
- [ ] Pilih tipe "Perkuliahan Tetap"
- [ ] Isi data peminjam
- [ ] Isi mata kuliah: Statistika Bisnis
- [ ] Isi dosen pengampu & NIP
- [ ] Isi software (opsional)
- [ ] Input peserta: 40
- [ ] **Cek:** Sistem harus rekomendasikan EL. 306 (kapasitas 40)
- [ ] Pilih tanggal & jam
- [ ] Upload scan KTM (PDF)
- [ ] Submit

#### 4. Smart Lab Recommendation
- [ ] Input peserta 20 → Lab 30 ✓
- [ ] Input peserta 35 → Lab 40 ✓
- [ ] Input peserta 55 → Lab 60 ✓
- [ ] Input peserta 100 → Warning ⚠

#### 5. Availability Check
- [ ] Pilih Senin 07:00-09:00 (ada perkuliahan) → ⚠ Terpakai
- [ ] Pilih Senin 13:00-15:00 (kosong) → ✓ Tersedia

#### 6. File Upload
- [ ] Upload PDF valid < 5MB → Success
- [ ] Upload PDF > 5MB → Error
- [ ] Upload file .docx → Error
- [ ] Upload tanpa file → Success (opsional)

#### 7. Validation
- [ ] Submit form kosong → Error messages
- [ ] Submit jam selesai < jam mulai → Error
- [ ] Submit tanggal kemarin → Error

#### 8. Success Page
- [ ] Tampil booking ID
- [ ] Tampil semua detail booking
- [ ] Status "Menunggu Persetujuan"
- [ ] Tombol "Kembali ke Beranda" berfungsi
- [ ] Tombol "Ajukan Peminjaman Lagi" berfungsi

#### 9. Responsive Design
- [ ] Buka di mobile (< 768px) → Layout stack vertikal
- [ ] Buka di tablet (768-1024px) → Layout hybrid
- [ ] Buka di desktop (> 1024px) → Layout 2 kolom

---

## 🎨 Color Theme

Sistem menggunakan warna identitas FEB UNDIP:

```
Primary:   Yellow-500  (#EAB308)
Secondary: Yellow-600  (#CA8A04)
Accent:    Gray-700    (#374151)
Success:   Green-500
Error:     Red-500
Warning:   Yellow-400
```

---

## 📊 Database Schema

### Tables

#### `labs`
```sql
- id (PK)
- name (EL. 301, EL. 306, etc.)
- capacity (30, 40, 50, 60, 80)
- description
```

#### `bookings`
```sql
- id (PK)
- lab_id (FK to labs)
- booking_type (enum: perkuliahan_tetap, perkuliahan_tidak_tetap, non_perkuliahan)
- nama_peminjam
- program_studi
- nim
- no_telpon
- alamat
- jenis_kegiatan (for non-perkuliahan)
- nama_kegiatan (for non-perkuliahan)
- jabatan
- kebutuhan_peralatan
- mata_kuliah (for perkuliahan)
- dosen_pengampu (for perkuliahan)
- nip_dosen (for perkuliahan)
- software_digunakan
- is_recurring (boolean, true for perkuliahan_tetap)
- tanggal (date)
- start_time
- end_time
- jumlah_peserta
- document_path (PDF file path)
- status (enum: pending, approved, rejected)
- approved_by (FK to users)
- admin_notes
- approved_at
```

#### `schedules`
```sql
- id (PK)
- lab_id (FK to labs)
- day (Senin, Selasa, etc.)
- start_time
- end_time
- course
- lecturer
- komting
- student_count
- type (regular, booking)
- booking_id (FK to bookings, nullable)
```

#### `users`
```sql
- id (PK)
- name
- email
- password
- role (asisten_lab, super_admin)
```

---

## 🔄 Workflow

### Peminjam (Public)
```
1. Buka landing page → Lihat jadwal
2. Klik "Ajukan Peminjaman"
3. Pilih tipe peminjaman
4. Isi form (conditional fields)
5. Input jumlah peserta → Sistem rekomendasikan lab
6. Pilih tanggal & jam → Sistem cek availability
7. Upload dokumen PDF
8. Submit → Status: Pending
9. Tunggu approval dari Asisten Lab
10. Dapat konfirmasi via telepon
```

### Asisten Lab (Admin)
```
1. Login ke dashboard
2. Lihat list booking pending
3. Review detail booking
4. Approve/Reject dengan catatan
5. Jika approve:
   - Perkuliahan tetap → Generate 16 schedule entries
   - Lainnya → Generate 1 schedule entry
6. Schedule masuk ke jadwal landing page
7. Peminjam dapat notifikasi
```

---

## 🚧 Next Steps (Roadmap)

### Phase 2: Admin Dashboard
- [ ] Create admin dashboard layout
- [ ] List pending bookings with filters
- [ ] Booking detail modal/page
- [ ] Approve/reject actions
- [ ] Auto-create schedule on approval
- [ ] Approval logging

### Phase 3: Recurring Schedules
- [ ] Generate 16 weeks schedule for perkuliahan_tetap
- [ ] Semester date picker
- [ ] Bulk schedule creation
- [ ] Schedule cancellation

### Phase 4: Notifications
- [ ] Email notification on approval/rejection
- [ ] SMS notification (optional)
- [ ] Reminder H-1 before booking
- [ ] Notification settings

### Phase 5: Reports & Export
- [ ] PDF export for booking confirmation
- [ ] Weekly schedule PDF
- [ ] Monthly booking report
- [ ] Usage statistics

### Phase 6: Advanced Features
- [ ] Calendar view for bookings
- [ ] Conflict resolution UI
- [ ] Booking history for peminjam
- [ ] Rating & feedback system

---

## 🐛 Known Issues

None at the moment. Report issues to the development team.

---

## 👥 User Roles

### Public (No Auth)
- View landing page & schedules
- Submit booking requests
- View booking confirmation

### Asisten Lab (Authenticated)
- All public features
- Access admin dashboard
- Approve/reject bookings
- Manage schedules
- View reports

### Super Admin (Authenticated)
- All Asisten Lab features
- Manage users
- System settings
- Advanced reports

---

## 📞 Support

Untuk pertanyaan atau bantuan:
- Email: lab.Digital@feb.undip.ac.id
- Telp: (024) 1234567
- Alamat: Fakultas Ekonomika dan Bisnis UNDIP

---

## 📝 License

Proprietary - FEB UNDIP © 2025

---

## 🎉 Credits

**Developed by:** Laboratorium dan Fasilitas Digital FEB UNDIP Team  
**Powered by:** Laravel, Tailwind CSS, Vite  
**Design:** FEB UNDIP Brand Identity

---

**Status Update:**
✅ Database structure: DONE  
✅ Booking form: DONE  
✅ Smart lab recommendation: DONE  
✅ Availability check: DONE  
✅ File upload: DONE  
✅ Success page: DONE  
✅ Landing page integration: DONE  
⏳ Admin dashboard: PENDING  
⏳ Notifications: PENDING  

**Ready for UAT (User Acceptance Testing)** 🚀
