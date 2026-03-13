# Business Rules - Sistem Peminjaman Lab FEB UNDIP

## 🎯 Aturan Bisnis Utama

### 1. **Akses Sistem**

#### Peminjam (Public - No Login)
- Mahasiswa, dosen, atau pihak eksternal bisa langsung mengajukan peminjaman
- Tidak perlu registrasi atau login
- Data peminjam diisi manual di form (nama, nim, prodi, telp, alamat)

#### Asisten Lab (Authenticated)
- Harus login untuk akses dashboard approval
- Role: Asisten Lab atau Super Admin
- Bisa approve/reject booking
- Bisa manage jadwal

---

### 2. **Tipe Peminjaman**

#### A. Perkuliahan Tetap
**Karakteristik:**
- Jadwal berulang setiap minggu di hari & jam yang sama
- Berlaku untuk 1 semester (±16 minggu)
- Prioritas tertinggi

**Field Wajib:**
- Mata kuliah
- Dosen pengampu
- NIP dosen
- Software yang digunakan (optional)
- Scan KTM koordinator mata kuliah (PDF)
- Jumlah peserta
- Hari & jam (akan berlaku setiap minggu)

**Proses Approval:**
1. Asisten Lab review permintaan
2. Jika approve → sistem auto-generate 16 schedule entries
3. Schedule masuk ke jadwal mingguan secara otomatis
4. Peminjam mendapat konfirmasi

**Contoh:**
```
Perkuliahan: Statistika Bisnis
Dosen: Dr. Budi Santoso
Jadwal: Senin 07:00-09:00
Periode: 16 minggu (1 semester)

Hasil Approve:
→ Schedule entry untuk 16 Senin berturut-turut
→ Muncul di jadwal landing page
```

#### B. Perkuliahan Tidak Tetap
**Karakteristik:**
- Hanya sekali waktu (tidak berulang)
- Contoh: Kuliah pengganti, guest lecture, ujian
- Prioritas tinggi

**Field Wajib:**
- Mata kuliah
- Dosen pengampu
- NIP dosen
- Software yang digunakan (optional)
- Scan KTM koordinator mata kuliah (PDF)
- Jumlah peserta
- Tanggal & jam spesifik

**Proses Approval:**
1. Asisten Lab review permintaan
2. Jika approve → sistem create 1 schedule entry
3. Schedule masuk ke jadwal landing page
4. Peminjam mendapat konfirmasi

#### C. Non-Perkuliahan
**Karakteristik:**
- Kegiatan selain perkuliahan
- Contoh: Seminar, workshop, rapat, pelatihan
- Prioritas normal

**Field Wajib:**
- Jenis kegiatan (Seminar/Workshop/Pelatihan/Rapat/Ujian/Lainnya)
- Nama kegiatan
- Jabatan peminjam
- Alamat peminjam
- Kebutuhan peralatan
- Dokumen pendukung (PDF)
- Jumlah peserta
- Tanggal & jam spesifik

**Proses Approval:**
1. Asisten Lab review permintaan
2. Cek ketersediaan lab
3. Jika approve → sistem create 1 schedule entry
4. Schedule masuk ke jadwal landing page
5. Peminjam mendapat konfirmasi

---

### 3. **Smart Lab Capacity Matching**

#### Logika Pencocokan
```
Jumlah Peserta → Lab yang Direkomendasikan
=============================================
1-30 peserta   → EL. 301 (kapasitas 30)
31-40 peserta  → EL. 306 (kapasitas 40)
41-50 peserta  → EL. 307 (kapasitas 50)
51-60 peserta  → EL. 309 (kapasitas 60)
61+ peserta    → EL. 3 01 (kapasitas 80) atau warning
```

#### Aturan Pembulatan
- Sistem mencari lab dengan kapasitas **minimal** yang bisa menampung
- Prioritas: Lab terkecil yang cukup
- Tujuan: Efisiensi penggunaan ruangan

**Contoh Kasus:**
```
Peserta 22:
  ✓ Rekomendasikan EL. 301 (kapasitas 30) ← DIPILIH
  ✗ Jangan EL. 309 (kapasitas 60) ← TERLALU BESAR

Peserta 35:
  ✗ EL. 301 (kapasitas 30) ← TIDAK CUKUP
  ✓ Rekomendasikan EL. 306 (kapasitas 40) ← DIPILIH

Peserta 45:
  ✗ EL. 306 (kapasitas 40) ← TIDAK CUKUP
  ✓ Rekomendasikan EL. 307 (kapasitas 50) ← DIPILIH
```

#### Over Capacity
Jika peserta > kapasitas semua lab:
- Tampilkan warning merah
- Disable submit button
- Saran: "Jumlah peserta melebihi kapasitas semua lab yang tersedia"

---

### 4. **Availability Check**

#### Konflik Jadwal
Lab tidak tersedia jika:
1. Sudah ada perkuliahan rutin di jam tersebut
2. Sudah ada booking yang di-approve di jam tersebut
3. Waktu overlap dengan jadwal existing

#### Real-time Check
Sistem check saat:
- User memilih lab
- User memilih tanggal
- User input jam mulai/selesai

**Contoh Konflik:**
```
Existing: Senin 07:00-09:00 (Statistika)
Request:  Senin 08:00-10:00 (Workshop)
Result:   ⚠ BENTROK (overlap 1 jam)

Existing: Senin 07:00-09:00 (Statistika)
Request:  Senin 09:00-11:00 (Workshop)
Result:   ✓ TERSEDIA (tidak overlap)
```

---

### 5. **Workflow Approval**

#### Status Booking
1. **Pending** (Default)
   - Booking baru masuk
   - Menunggu review asisten lab
   - Belum masuk jadwal

2. **Approved**
   - Sudah disetujui asisten lab
   - Schedule entry otomatis dibuat
   - Masuk ke jadwal landing page
   - Peminjam dapat konfirmasi

3. **Rejected**
   - Ditolak oleh asisten lab
   - Ada catatan penolakan
   - Peminjam dapat konfirmasi + alasan

#### Logging
Setiap approval/rejection dicatat:
- Siapa yang approve (`approved_by`)
- Kapan di-approve (`approved_at`)
- Catatan admin (`admin_notes`)

---

### 6. **Dokumen Upload**

#### Untuk Perkuliahan
**File yang diupload:** Scan KTM Koordinator Mata Kuliah
**Tujuan:** Verifikasi bahwa permohonan benar dari koordinator MK
**Format:** PDF
**Max size:** 5MB

#### Untuk Non-Perkuliahan
**File yang diupload:** Dokumen Pendukung (sudah digabung)
**Isi dokumen:**
- Surat permohonan
- Proposal kegiatan
- Dokumen pendukung lainnya

**Format:** PDF
**Max size:** 5MB

#### Validasi
- Extension harus `.pdf`
- Size max 5MB (5120 KB)
- Jika tidak valid → error message
- File disimpan di `storage/app/public/booking-documents/`

---

### 7. **Recurring Schedule (Perkuliahan Tetap)**

#### Cara Kerja
Saat booking perkuliahan tetap di-approve:

```php
// Contoh: Senin 07:00-09:00 (16 minggu)
$startDate = $booking->tanggal; // Senin pertama
$weeks = 16; // 1 semester

for ($i = 0; $i < $weeks; $i++) {
    $scheduleDate = $startDate->addWeeks($i);
    
    Schedule::create([
        'lab_id' => $booking->lab_id,
        'day' => 'Senin',
        'start_time' => '07:00',
        'end_time' => '09:00',
        'course' => $booking->mata_kuliah,
        'lecturer' => $booking->dosen_pengampu,
        'type' => 'regular',
        'booking_id' => $booking->id
    ]);
}
```

#### Pembatalan
Jika perkuliahan tetap dibatalkan:
- Hapus semua schedule entries yang terkait
- Atau: Update status menjadi "cancelled"
- Beri notifikasi ke peminjam

---

### 8. **Prioritas Peminjaman**

#### Urutan Prioritas (Highest to Lowest)
1. **Perkuliahan Tetap** (Regular lectures - 1 semester)
2. **Perkuliahan Tidak Tetap** (One-time lectures)
3. **Non-Perkuliahan** (Events, seminars, etc.)

#### Conflict Resolution
Jika ada konflik jadwal:
- Perkuliahan tetap > Perkuliahan tidak tetap
- Perkuliahan tidak tetap > Non-perkuliahan
- First come first served untuk tipe yang sama

---

### 9. **Notifikasi (Future)**

#### Email/SMS ke Peminjam
**Event: Booking Approved**
```
Subject: Peminjaman Lab Disetujui

Halo [Nama],

Permintaan peminjaman Anda telah disetujui!

Detail:
- Lab: [Nama Lab]
- Tanggal: [Tanggal]
- Jam: [Start] - [End]
- Kegiatan: [Nama Kegiatan]

Silakan datang tepat waktu.
Hubungi kami jika ada pertanyaan.

Laboratorium dan Fasilitas Digital FEB UNDIP
```

**Event: Booking Rejected**
```
Subject: Peminjaman Lab Ditolak

Halo [Nama],

Maaf, permintaan peminjaman Anda tidak dapat disetujui.

Alasan: [Admin Notes]

Silakan ajukan kembali dengan penyesuaian.

Laboratorium dan Fasilitas Digital FEB UNDIP
```

#### Reminder
**H-1 sebelum jadwal:**
```
Subject: Reminder: Peminjaman Lab Besok

Halo [Nama],

Mengingatkan jadwal peminjaman lab Anda:

- Lab: [Nama Lab]
- Tanggal: BESOK, [Tanggal]
- Jam: [Start] - [End]

Jangan lupa membawa dokumen pendukung.

Laboratorium dan Fasilitas Digital FEB UNDIP
```

---

### 10. **Data Validation Rules**

```php
// Booking Request Validation
[
    'booking_type' => 'required|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
    'nama_peminjam' => 'required|string|max:255',
    'program_studi' => 'required|string|max:255',
    'nim' => 'required|string|max:50',
    'no_telpon' => 'required|string|max:20',
    'alamat' => 'nullable|string',
    'lab_id' => 'required|exists:labs,id',
    'tanggal' => 'required|date|after_or_equal:today',
    'start_time' => 'required|date_format:H:i',
    'end_time' => 'required|date_format:H:i|after:start_time',
    'jumlah_peserta' => 'required|integer|min:1',
    'document' => 'nullable|file|mimes:pdf|max:5120',
    
    // Conditional for Perkuliahan
    'mata_kuliah' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap',
    'dosen_pengampu' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap',
    'nip_dosen' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap',
    
    // Conditional for Non-Perkuliahan
    'jenis_kegiatan' => 'required_if:booking_type,non_perkuliahan',
    'nama_kegiatan' => 'required_if:booking_type,non_perkuliahan',
]
```

---

## 📊 Database Constraints

### Foreign Keys
- `bookings.lab_id` → `labs.id` (ON DELETE CASCADE)
- `bookings.approved_by` → `users.id` (ON DELETE SET NULL)
- `schedules.lab_id` → `labs.id` (ON DELETE CASCADE)
- `schedules.booking_id` → `bookings.id` (ON DELETE CASCADE)

### Indexes
- `bookings.status` (untuk filter pending/approved/rejected)
- `bookings.tanggal` (untuk filter by date)
- `schedules.day` + `schedules.lab_id` (untuk lookup jadwal)

---

## 🔐 Security Considerations

### Public Form
- CSRF token protection
- File upload validation (PDF only, max 5MB)
- SQL injection protection (Eloquent ORM)
- XSS protection (Blade escaping)

### Admin Dashboard
- Authentication required
- Role-based access control
- Audit logging (who approved what)

---

Semua business rules ini sudah diimplementasikan dalam kode! 🚀
