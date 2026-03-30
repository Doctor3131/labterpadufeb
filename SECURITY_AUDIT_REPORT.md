# Laporan Audit Keamanan Sistem Peminjaman Ruang Lab

**Tanggal Audit:** 2025-01-20  
**Ruang Lingkup:** Sistem Booking/Peminjaman Ruang Laboratorium  
**Status:** ✅ AMAN - Beberapa Rekomendasi Perbaikan

---

## 📋 Ringkasan Eksekutif

Sistem peminjaman ruang lab telah diaudit secara mendalam untuk mengidentifikasi kerentanan keamanan dan bug. Secara keseluruhan, sistem sudah menerapkan praktik keamanan yang baik dengan beberapa area yang memerlukan peningkatan.

**Temuan Utama:**
- ✅ **6 Aspek Aman** - Sistem memiliki fondasi keamanan yang solid
- ⚠️ **4 Kerentanan Sedang** - Memerlukan perbaikan untuk meningkatkan keamanan
- 🔴 **1 Kerentanan Tinggi** - Memerlukan perhatian segera

---

## 🔐 1. Autentikasi & Otorisasi

### ✅ Aspek Yang Sudah Aman:

**1.1 Middleware Protection**
- ✅ Route admin dilindungi dengan middleware `auth` dan `admin`
- ✅ Implementasi di [routes/web.php](routes/web.php#L52-L117)
```php
Route::middleware(['auth', 'admin'])->group(function () {
    // Protected admin routes
});
```

**1.2 Role-Based Access Control**
- ✅ Metode `isAdmin()` dan `isSuperAdmin()` di [User.php](app/Models/User.php#L56-L67)
- ✅ Middleware custom di [EnsureAdmin.php](app/Http/Middleware/EnsureAdmin.php#L17-L18)

**1.3 Rate Limiting**
- ✅ Login throttling: `throttle:10,1` (10 attempts per minute)
- ✅ Booking submission: `throttle:10,1`
- ✅ Feedback submission: `throttle:5,1`
- ✅ AJAX requests: `throttle:60,1`

### ⚠️ Kerentanan Yang Ditemukan:

**1.1 [SEDANG] Tidak Ada Session Timeout**
- **Masalah:** Session dapat aktif tanpa batas waktu
- **Dampak:** Session hijacking lebih mudah jika device tidak logout
- **Lokasi:** [config/session.php](config/session.php)
- **Rekomendasi:**
```php
// config/session.php
'lifetime' => 120, // 2 jam (dari default 120)
'expire_on_close' => true, // Logout saat browser ditutup
```

**1.2 [SEDANG] Tidak Ada Password Policy**
- **Masalah:** Tidak ada validasi kekuatan password saat registrasi
- **Dampak:** User bisa menggunakan password lemah
- **Rekomendasi:** Tambahkan validasi:
```php
'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
```

---

## 🔒 2. Race Conditions & Concurrent Access

### ✅ Aspek Yang Sudah Aman:

**2.1 Database Locking**
- ✅ Menggunakan `lockForUpdate()` di [BookingController.php](app/Http/Controllers/BookingController.php#L166)
```php
$lab = Lab::lockForUpdate()->findOrFail($validated['lab_id']);
```

**2.2 Transaction Wrapping**
- ✅ Semua operasi booking dibungkus dalam `DB::transaction()`
- ✅ Atomicity terjaga (all-or-nothing)

### ⚠️ Kerentanan Yang Ditemukan:

**2.1 [TINGGI] Race Condition pada Approval**
- **Masalah:** [AdminController::approve()](app/Http/Controllers/AdminController.php#L56-L148) tidak menggunakan lock
- **Dampak:** Dua admin bisa menyetujui booking yang sama secara simultan, menyebabkan jadwal ganda
- **Skenario Eksploitasi:**
  1. Admin A mulai approve booking #123
  2. Admin B juga mulai approve booking #123
  3. Kedua request lolos validasi conflict check
  4. Kedua request membuat schedule entry → DUPLIKAT!

- **Rekomendasi - PERBAIKAN URGENT:**
```php
public function approve($id)
{
    DB::transaction(function () use ($id) {
        // CRITICAL: Lock booking record
        $booking = Booking::lockForUpdate()->findOrFail($id);
        
        // Check if already processed
        if ($booking->status !== 'pending') {
            throw new \Exception('Booking sudah diproses sebelumnya.');
        }
        
        // Lock lab as well
        $lab = Lab::lockForUpdate()->findOrFail($booking->lab_id);
        
        // Rest of approval logic...
    });
}
```

**2.2 [SEDANG] No Lock pada Reject**
- **Masalah:** [AdminController::reject()](app/Http/Controllers/AdminController.php#L156-L195) juga tidak menggunakan lock
- **Dampak:** Similar race condition saat rejection
- **Rekomendasi:** Gunakan pattern yang sama dengan approval

---

## 💉 3. SQL Injection & Input Validation

### ✅ Aspek Yang Sudah Aman:

**3.1 Eloquent ORM Protection**
- ✅ Semua query menggunakan Eloquent (parameterized queries)
- ✅ Tidak ada concatenation manual di query

**3.2 Raw Queries Aman**
- ✅ `selectRaw()`, `orderByRaw()` tidak menggunakan user input
- ✅ Contoh di [InventoryService.php](app/Services/InventoryService.php#L419):
```php
->selectRaw('items.id as item_id, ...') // Static string, aman
```

**3.3 Comprehensive Validation**
- ✅ Validasi lengkap di [BookingController::store()](app/Http/Controllers/BookingController.php#L58-L146)
- ✅ Conditional validation berdasarkan `booking_type`
- ✅ Enum validation untuk field seperti `day`, `status`, `booking_type`

### ⚠️ Kerentanan Yang Ditemukan:

**3.1 [RENDAH] Mass Assignment Vulnerability**
- **Masalah:** Beberapa model memiliki `fillable` yang luas
- **Dampak:** Potential untuk mengubah field yang tidak seharusnya
- **Contoh:** [Lab.php](app/Models/Lab.php) memiliki semua field di fillable
- **Rekomendasi:** Gunakan `$guarded` untuk field sensitif:
```php
protected $guarded = ['id', 'created_at', 'updated_at'];
```

---

## ⏰ 4. Logika Deteksi Konflik Waktu

### ✅ Aspek Yang Sudah Aman:

**4.1 Two-Tier Conflict Detection**
- ✅ Check schedules (recurring) + bookings (one-time)
- ✅ Implementasi di [Lab::isAvailable()](app/Models/Lab.php#L72-L128)

**4.2 Time Overlap Logic**
- ✅ Menggunakan query yang benar untuk deteksi overlap:
```php
->whereTime('start_time', '<', $endTime)
->whereTime('end_time', '>', $startTime)
```

**4.3 Date Range Validation**
- ✅ Check permanent schedules (null end_date)
- ✅ Check temporary schedules (specific date ranges)

### ⚠️ Kerentanan Yang Ditemukan:

**4.1 [SEDANG] Status Inconsistency Bug**
- **Masalah:** [Lab::isAvailable()](app/Models/Lab.php#L101-L117) hanya check `status='pending'` bookings
- **Dampak:** Approved bookings yang sudah punya schedule bisa ter-override
- **Skenario:**
  1. Booking A disetujui → status: approved, schedule created
  2. isAvailable() hanya check pending bookings
  3. Booking B dengan waktu sama bisa lolos karena A sudah bukan "pending"
  4. Saat B diapprove, baru muncul conflict!

- **Perbaikan di Line 101:**
```php
// SEBELUM (BUG):
->where('status', 'pending')

// SESUDAH (FIXED):
->whereIn('status', ['pending', 'approved'])
```

**4.2 [RENDAH] Edge Case: Same Time Endpoints**
- **Masalah:** Booking dengan end_time == start_time booking lain dianggap konflik
- **Contoh:** Booking A: 08:00-10:00, Booking B: 10:00-12:00 → Konflik!
- **Dampak:** User tidak bisa booking back-to-back
- **Rekomendasi:** Ubah operator jika back-to-back diperbolehkan:
```php
// Jika back-to-back allowed:
->whereTime('start_time', '<', $endTime)
->whereTime('end_time', '>', $startTime)
// Menjadi:
->whereTime('start_time', '<=', $endTime)
->whereTime('end_time', '>', $startTime)
```

---

## 📁 5. File Upload Security

### ✅ Aspek Yang Sudah Aman:

**5.1 File Type Validation**
- ✅ Hanya menerima PDF: `mimes:pdf`
- ✅ Validasi di [BookingController.php](app/Http/Controllers/BookingController.php#L139)

**5.2 File Size Limit**
- ✅ Max 5MB: `max:5120` (5120KB)
- ✅ Mencegah DoS via large file uploads

**5.3 Secure Storage**
- ✅ File disimpan di `storage/app/public/booking-documents`
- ✅ Tidak langsung di public root

### ⚠️ Kerentanan Yang Ditemukan:

**5.1 [SEDANG] No File Name Sanitization**
- **Masalah:** Filename dari user langsung digunakan
- **Dampak:** Potential untuk filename collision atau injection
- **Rekomendasi:** Generate unique filename:
```php
if ($request->hasFile('document')) {
    $file = $request->file('document');
    $filename = time() . '_' . Str::random(10) . '.' . $file->extension();
    $path = $file->storeAs('booking-documents', $filename, 'public');
    $validated['document_path'] = $path;
}
```

**5.2 [RENDAH] Publicly Accessible Files**
- **Masalah:** Files di `public` storage bisa diakses siapa saja jika tahu path
- **Dampak:** Privacy breach - dokumen KTM/surat bisa dilihat orang lain
- **Rekomendasi:** Implementasi controller untuk serve files:
```php
Route::get('/booking/document/{token}', function($token) {
    $booking = Booking::where('tracking_token', $token)->firstOrFail();
    return Storage::download($booking->document_path);
})->name('booking.document');
```

---

## 🔓 6. Data Exposure & Privacy

### ✅ Aspek Yang Sudah Aman:

**6.1 Tracking Token Security**
- ✅ Menggunakan `bin2hex(random_bytes(16))` untuk generate token
- ✅ 32 character hex string (128-bit entropy)
- ✅ Akses booking menggunakan token, bukan ID

**6.2 Password Hashing**
- ✅ Password di-hash otomatis oleh Laravel
- ✅ Menggunakan bcrypt (default Laravel)

**6.3 CSRF Protection**
- ✅ Semua POST request memerlukan CSRF token
- ✅ Laravel middleware otomatis validasi

### ⚠️ Kerentanan Yang Ditemukan:

**6.1 [RENDAH] Sensitive Data Logging**
- **Masalah:** [AdminController.php](app/Http/Controllers/AdminController.php#L149-L152) log user input
```php
Log::info('Reject booking called', [
    'booking_id' => $id,
    'reason' => $request->rejection_reason
]);
```
- **Dampak:** Log file berisi data sensitif (NIM, phone numbers, etc.)
- **Rekomendasi:** Hindari logging PII (Personally Identifiable Information)

**6.2 [RENDAH] No HTTPS Enforcement**
- **Masalah:** Tidak ada redirect HTTP → HTTPS di production
- **Dampak:** Data bisa ter-intercept jika user akses via HTTP
- **Rekomendasi:** Tambahkan di [AppServiceProvider.php](app/Providers/AppServiceProvider.php):
```php
if ($this->app->environment('production')) {
    \URL::forceScheme('https');
}
```

---

## 🛡️ Rekomendasi Prioritas Perbaikan

### 🔴 URGENT (1-3 Hari):
1. **Fix Race Condition pada Approval/Rejection**
   - Tambahkan `lockForUpdate()` di AdminController
   - Implementasi status check sebelum proses

2. **Fix Status Check Bug di isAvailable()**
   - Ubah `where('status', 'pending')` menjadi `whereIn('status', ['pending', 'approved'])`

### ⚠️ PENTING (1-2 Minggu):
3. **File Upload Security**
   - Sanitize filename dengan unique identifier
   - Implementasi private file serving

4. **Session Security**
   - Set session timeout
   - Enable expire_on_close

### ℹ️ OPTIONAL (Future Improvement):
5. **Password Policy**
   - Tambahkan validasi password strength

6. **HTTPS Enforcement**
   - Force HTTPS di production

7. **Logging Privacy**
   - Hilangkan PII dari log files

---

## 📊 Skor Keamanan

| Aspek | Status | Skor |
|-------|--------|------|
| Autentikasi & Otorisasi | ⚠️ Baik | 8/10 |
| Race Conditions | 🔴 Perlu Perbaikan | 5/10 |
| SQL Injection | ✅ Sangat Baik | 9/10 |
| Deteksi Konflik | ⚠️ Baik | 7/10 |
| File Upload | ⚠️ Baik | 7/10 |
| Privacy & Data Exposure | ✅ Baik | 8/10 |
| **TOTAL** | ⚠️ **Baik** | **7.3/10** |

---

## ✅ Checklist Implementasi Perbaikan

### Urgent Fixes:
- [ ] Tambahkan `lockForUpdate()` di `AdminController::approve()`
- [ ] Tambahkan `lockForUpdate()` di `AdminController::reject()`
- [ ] Tambahkan status check setelah lock
- [ ] Fix `isAvailable()` untuk check approved bookings
- [ ] Test concurrent approval scenario

### Important Fixes:
- [ ] Implementasi unique filename generation
- [ ] Buat route untuk private file serving
- [ ] Set session lifetime & expire_on_close
- [ ] Implementasi password policy

### Optional Improvements:
- [ ] Force HTTPS di production
- [ ] Audit & clean up logging
- [ ] Implementasi audit trail untuk sensitive actions
- [ ] Add automated security tests

---

## 📝 Catatan Testing

**Skenario Test Yang Direkomendasikan:**

1. **Race Condition Test:**
   ```bash
   # Simulasi concurrent approval
   # Gunakan 2 browser/tab berbeda
   # Login sebagai 2 admin berbeda
   # Approve booking yang sama secara bersamaan
   ```

2. **File Upload Test:**
   ```bash
   # Upload file dengan extension ganda: file.pdf.php
   # Upload file dengan special characters: ../../../etc/passwd.pdf
   # Upload file > 5MB
   ```

3. **Time Conflict Test:**
   ```bash
   # Book A: 08:00-10:00
   # Book B: 10:00-12:00 (should this conflict?)
   # Book C: 09:00-11:00 (must conflict with A)
   ```

---

**Audit dilakukan oleh:** GitHub Copilot AI Assistant  
**Platform:** Laravel 12.43.1, PHP 8.2.12, MySQL  
**Metode:** Static Code Analysis + Logic Review

---

## 🔍 Lampiran: Code Snippets untuk Fix

### Fix 1: Race Condition Protection
```php
// File: app/Http/Controllers/AdminController.php

public function approve($id)
{
    return DB::transaction(function () use ($id) {
        // 🔒 CRITICAL: Lock untuk prevent race condition
        $booking = Booking::lockForUpdate()->findOrFail($id);
        
        // ✅ Check status setelah lock
        if ($booking->status !== 'pending') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Peminjaman sudah diproses sebelumnya.');
        }
        
        // 🔒 Lock lab juga untuk consistency
        $lab = Lab::lockForUpdate()->findOrFail($booking->lab_id);
        
        // Conflict check...
        $bookingDate = \Carbon\Carbon::parse($booking->booking_date);
        $conflictCheck = $this->checkScheduleConflict(
            $booking->lab_id,
            $booking->day,
            $booking->start_time,
            $booking->end_time,
            $bookingDate->format('Y-m-d'),
            $booking->is_recurring ? null : $bookingDate->format('Y-m-d')
        );

        if ($conflictCheck) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Tidak dapat menyetujui peminjaman: ' . $conflictCheck);
        }
        
        // Update booking & create schedule...
        // (existing code)
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil disetujui!');
    });
}

public function reject(Request $request, $id)
{
    $request->validate([
        'rejection_reason' => 'required|string|max:500'
    ]);

    return DB::transaction(function () use ($id, $request) {
        // 🔒 Lock untuk prevent race condition
        $booking = Booking::lockForUpdate()->findOrFail($id);
        
        // ✅ Check status
        if ($booking->status !== 'pending') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Peminjaman sudah diproses sebelumnya.');
        }
        
        // Delete related schedule & update...
        // (existing code)
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil ditolak.');
    });
}
```

### Fix 2: Status Check Bug
```php
// File: app/Models/Lab.php
// Line 101-117

// Check one-time bookings (only if date is provided)
if ($date) {
    $hasBookingConflict = $this->bookings()
        ->where('booking_date', $date)
        ->whereIn('status', ['pending', 'approved']) // 🔧 FIX: Check both pending & approved
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                // New booking starts during existing booking
                $q->where('start_time', '<=', $startTime)
                  ->where('end_time', '>', $startTime);
            })
            ->orWhere(function ($q) use ($startTime, $endTime) {
                // New booking ends during existing booking
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>=', $endTime);
            })
            ->orWhere(function ($q) use ($startTime, $endTime) {
                // New booking completely covers existing booking
                $q->where('start_time', '>=', $startTime)
                  ->where('end_time', '<=', $endTime);
            });
        })
        ->exists();

    if ($hasBookingConflict) {
        return false;
    }
}
```

### Fix 3: Secure File Upload
```php
// File: app/Http/Controllers/BookingController.php

// Handle document upload
if ($request->hasFile('document')) {
    $file = $request->file('document');
    
    // 🔧 Generate unique, secure filename
    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $sanitizedName = \Str::slug($originalName); // Remove special chars
    $filename = $sanitizedName . '_' . time() . '_' . \Str::random(8) . '.pdf';
    
    // Store dengan nama custom
    $path = $file->storeAs('booking-documents', $filename, 'public');
    $validated['document_path'] = $path;
}
```

---

**End of Security Audit Report**
