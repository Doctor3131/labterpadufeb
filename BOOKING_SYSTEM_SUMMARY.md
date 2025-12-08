# Sistem Peminjaman Laboratorium - Summary

## ✅ Perubahan yang Sudah Diimplementasi

### 1. **Database Structure**

#### Tabel `bookings` (Updated)
Telah diperbaharui dengan field-field berikut:

**Data Peminjam:**
- `nama_peminjam` - Nama lengkap peminjam
- `program_studi` - Program studi peminjam
- `nim` - NIM peminjam
- `no_telpon` - Nomor telepon peminjam
- `alamat` - Alamat tempat tinggal peminjam

**Tipe Peminjaman:**
- `booking_type` - Enum: `perkuliahan_tetap`, `perkuliahan_tidak_tetap`, `non_perkuliahan`
- `is_recurring` - Boolean untuk perkuliahan tetap (akan berulang setiap minggu)

**Detail Non-Perkuliahan:**
- `jenis_kegiatan` - Enum: Seminar, Workshop, Pelatihan, Rapat, Ujian, Lainnya
- `jabatan` - Jabatan peminjam
- `kebutuhan_peralatan` - Deskripsi peralatan yang dibutuhkan
- `nama_kegiatan` - Nama kegiatan yang akan dilakukan

**Detail Perkuliahan:**
- `mata_kuliah` - Nama mata kuliah
- `dosen_pengampu` - Nama dosen/instruktur
- `nip_dosen` - NIP dosen/instruktur
- `software_digunakan` - Software yang akan digunakan (opsional)

**Jadwal & Lab:**
- `lab_id` - Foreign key ke tabel labs
- `tanggal` - Tanggal peminjaman
- `start_time` - Jam mulai
- `end_time` - Jam selesai
- `jumlah_peserta` - Jumlah peserta

**Dokumen & Approval:**
- `document_path` - Path file PDF (untuk scan KTM atau dokumen pendukung)
- `status` - Enum: pending, approved, rejected
- `approved_by` - Foreign key ke users (asisten lab yang approve)
- `admin_notes` - Catatan dari admin
- `approved_at` - Timestamp approval

**CATATAN PENTING:** `user_id` sudah dihapus karena sistem tidak memerlukan login untuk peminjam!

### 2. **Smart Lab Capacity Matching**

Sistem otomatis merekomendasikan lab berdasarkan jumlah peserta:
- Input jumlah peserta → sistem mencari lab dengan kapasitas minimal yang sesuai
- Urutan prioritas: Lab terkecil yang bisa menampung
- Contoh: 22 peserta → sistem sarankan lab kapasitas 30 (bukan 50)

**Logika Pembulatan Ke Atas:**
```
Peserta 22 → Lab Kapasitas 30 ✓
Peserta 35 → Lab Kapasitas 40 ✓
Peserta 45 → Lab Kapasitas 50 ✓
```

### 3. **Form Peminjaman Publik**

**URL:** `/booking`

**Fitur:**
- ✅ Tidak perlu login (public access)
- ✅ Conditional fields berdasarkan tipe peminjaman
- ✅ Auto-suggest lab berdasarkan jumlah peserta
- ✅ Real-time availability check
- ✅ Upload dokumen PDF (max 5MB)
- ✅ Responsive design dengan Tailwind CSS

**Tipe Peminjaman:**
1. **Perkuliahan Tetap** (Setiap minggu di jadwal yang sama)
   - Mata kuliah
   - Dosen pengampu & NIP
   - Software yang digunakan
   - Upload scan KTM koordinator mata kuliah

2. **Perkuliahan Tidak Tetap** (Sekali waktu)
   - Mata kuliah
   - Dosen pengampu & NIP
   - Software yang digunakan
   - Upload scan KTM koordinator mata kuliah

3. **Non-Perkuliahan**
   - Jenis kegiatan (dropdown: Seminar, Workshop, dll)
   - Nama kegiatan
   - Jabatan peminjam
   - Kebutuhan peralatan
   - Upload dokumen pendukung

### 4. **Real-time Availability Check**

Sistem mengecek ketersediaan lab secara real-time:
- Cek jadwal perkuliahan yang sudah ada
- Cek booking yang sudah di-approve
- Tampilkan status: ✓ Tersedia / ⚠ Sudah terpakai

### 5. **Success Page**

Setelah submit, peminjam akan melihat:
- Confirmation dengan ID booking
- Ringkasan detail peminjaman
- Status: Menunggu persetujuan
- Informasi: Akan dihubungi via telepon

### 6. **Navigasi**

**Landing Page:**
- Tombol "Ajukan Peminjaman" → mengarah ke `/booking`
- Tombol "Login Asisten Lab" → mengarah ke `/login`
- Tombol "Daftar" dihapus (hanya asisten lab yang perlu login)

---

## 🚀 Routes yang Tersedia

### Public Routes (No Auth)
```
GET  /                        → Landing page dengan jadwal
GET  /booking                 → Form peminjaman
POST /booking                 → Submit peminjaman
GET  /booking/success/{id}    → Halaman sukses
POST /booking/available-labs  → API cek lab yang cocok
POST /booking/check-availability → API cek ketersediaan
```

### Auth Routes (Untuk Asisten Lab)
```
GET  /login                   → Login asisten lab
POST /login                   → Proses login
GET  /dashboard               → Dashboard asisten lab (akan dibuat)
POST /logout                  → Logout
```

---

## 📝 Catatan Implementasi

### Yang Sudah Selesai ✅
1. Database migration dengan semua field yang dibutuhkan
2. Booking model dengan relationships dan helper methods
3. BookingController dengan CRUD dan availability check
4. Form view dengan conditional fields
5. Success page dengan detail booking
6. Smart lab capacity matching
7. Real-time availability check
8. Navbar updated (Login Asisten Lab only)

### Yang Belum Dibuat (Next Steps) ❌
1. **Admin Dashboard** untuk asisten lab
   - List booking yang pending
   - Approve/reject dengan catatan
   - Auto-create schedule entry saat approve
   - Logging approval history

2. **Recurring Schedule** untuk perkuliahan tetap
   - Auto-generate schedule untuk semester berjalan
   - Pilih tanggal mulai & tanggal selesai

3. **Notifikasi**
   - Email/SMS ke peminjam saat di-approve/reject
   - Reminder H-1 sebelum jadwal

4. **Export PDF**
   - Cetak bukti booking
   - Cetak jadwal mingguan

---

## 🔄 Cara Menggunakan

### Untuk Peminjam (Mahasiswa/Dosen):
1. Buka website Lab Terpadu FEB UNDIP
2. Klik "Ajukan Peminjaman"
3. Pilih tipe peminjaman
4. Isi data peminjam & detail kegiatan
5. Masukkan jumlah peserta (sistem akan rekomendasikan lab)
6. Pilih tanggal & jam
7. Upload dokumen
8. Submit dan tunggu persetujuan

### Untuk Asisten Lab:
1. Login melalui "Login Asisten Lab"
2. Dashboard akan menampilkan booking pending
3. Review detail booking
4. Approve/reject dengan catatan
5. Saat approve, jadwal otomatis masuk ke schedule table

---

## 🗄️ Database Relationships

```
users (asisten lab)
  └─ has many bookings (as approver via approved_by)

labs
  ├─ has many schedules
  └─ has many bookings

bookings
  ├─ belongs to lab
  ├─ belongs to user (approver)
  └─ has one schedule (after approval)

schedules
  ├─ belongs to lab
  └─ belongs to booking (nullable)
```

---

## 💡 Business Logic

### Perkuliahan Tetap
- `is_recurring = true`
- Saat di-approve, sistem create multiple schedule entries (1 semester)
- Contoh: Senin 07:00-09:00 → Generate 16 schedule entries (16 minggu)

### Perkuliahan Tidak Tetap
- `is_recurring = false`
- Saat di-approve, sistem create 1 schedule entry
- Hanya berlaku di tanggal yang dipilih

### Non-Perkuliahan
- `is_recurring = false`
- Saat di-approve, sistem create 1 schedule entry
- Hanya berlaku di tanggal yang dipilih

---

## 🎨 UI/UX Features

1. **Conditional Form Fields**
   - Form berubah dinamis sesuai tipe peminjaman
   - Label dokumen berubah (KTM vs Dokumen Pendukung)

2. **Smart Lab Selection**
   - Auto-recommend lab yang paling pas
   - Warna hijau untuk rekomendasi
   - Warna merah jika over capacity

3. **Real-time Feedback**
   - Loading state saat check availability
   - Instant validation
   - Success/error messages

4. **Responsive Design**
   - Mobile-first approach
   - Touch-friendly buttons
   - Adaptive layouts

---

## 📱 Testing Checklist

Sebelum go-live, test skenario berikut:

### Form Submission
- [ ] Submit perkuliahan tetap dengan semua field
- [ ] Submit perkuliahan tidak tetap
- [ ] Submit non-perkuliahan dengan jenis kegiatan berbeda
- [ ] Upload PDF > 5MB (harus ditolak)
- [ ] Upload file non-PDF (harus ditolak)
- [ ] Submit tanpa mengisi field required (harus error)

### Lab Selection
- [ ] Input peserta 20 → harus sarankan lab 30
- [ ] Input peserta 55 → harus sarankan lab 60
- [ ] Input peserta 100 → harus warning over capacity

### Availability Check
- [ ] Pilih jadwal yang kosong (harus ✓ Tersedia)
- [ ] Pilih jadwal yang sudah ada perkuliahan (harus ⚠ Terpakai)
- [ ] Pilih jadwal yang sudah ada booking approved (harus ⚠ Terpakai)

### Success Page
- [ ] Redirect ke success page setelah submit
- [ ] Tampilkan detail booking dengan benar
- [ ] Status "Menunggu Persetujuan" muncul

---

Semua implementasi sudah selesai dan siap untuk ditest! 🎉
