# Panduan Penggunaan Sistem Surat Peminjaman Barang

## ✅ Fitur yang Telah Dibuat

Sistem peminjaman aset telah dilengkapi dengan fitur generate **Surat Peminjaman Barang** otomatis sesuai format FEB UNDIP.

### 🎯 Alur Kerja

#### 1. **User Mengisi Form Peminjaman (PIHAK KEDUA)**
- Akses: `http://localhost/asset-borrowing`
- User mengisi:
  - Data diri (nama, jabatan, **alamat**, telepon, email)
  - Pilih laboratorium
  - Pilih barang yang akan dipinjam
  - **Merk/Tipe barang**
  - **Kondisi barang** (Baik, Cukup, Lengkap)
  - **Keterangan tambahan**
  - Tanggal pinjam dan kembali
  - Tujuan peminjaman

#### 2. **Admin Melengkapi Data (PIHAK PERTAMA)**
- Login sebagai admin
- Akses: `Admin Dashboard` → `Peminjaman Aset` (menu ungu di navbar)
- Klik detail pada peminjaman yang ingin diproses
- Isi data **PIHAK PERTAMA** (Penanggung Jawab):
  - Nama (contoh: `Zola Gio Amri sakhi`)
  - Jabatan (contoh: `Asisten UPK`)
  - Alamat (contoh: `Semarang`)
  - Telepon Kantor (contoh: `+62 877-4119-1305`)
  - Tanggal Surat (opsional, default hari ini)
- Klik **"Simpan & Generate Surat"**

#### 3. **Sistem Generate Surat Otomatis**
✓ Nomor surat dibuat otomatis: `003/SPB/UPKFEB/IX/2025`  
✓ PDF surat peminjaman dibuat dengan format resmi FEB UNDIP  
✓ Admin dapat **Preview** atau **Download** PDF  

### 📋 Format Surat Peminjaman

Surat yang digenerate mencakup:
- ✓ Header dengan Logo FEB UNDIP
- ✓ Nomor Surat Otomatis
- ✓ Data PIHAK PERTAMA (Admin/Penanggung Jawab)
- ✓ Data PIHAK KEDUA (Peminjam)
- ✓ Tabel barang dengan:
  - Nomor urut
  - Nama barang
  - Merk/Tipe
  - Jumlah
  - Kondisi (Baik ☑ / Cukup ☐ / Lengkap ☑)
  - Keterangan
- ✓ Tanggal dan tempat
- ✓ Ruang tanda tangan PIHAK KEDUA dan PIHAK PERTAMA

### 🔧 Database Changes

**Migration telah dijalankan:**
- ✓ Field baru di tabel `asset_borrowings`:
  - `document_number`, `first_party_name`, `first_party_position`
  - `first_party_address`, `first_party_phone`, `document_date`
  - `borrower_address`, `generated_document_path`

- ✓ Field baru di tabel `asset_borrowing_items`:
  - `brand_type`, `condition_good`, `condition_adequate`
  - `condition_complete`, `remarks`

### 📦 Package Installed

✓ `barryvdh/laravel-dompdf` v3.1 - untuk generate PDF

### 🎨 UI/UX

**User Interface:**
- ✓ Form publik untuk user di `/asset-borrowing`
- ✓ Halaman admin list peminjaman di `/admin/asset-borrowings`
- ✓ Halaman detail dengan form PIHAK PERTAMA
- ✓ Tombol Preview & Download PDF
- ✓ Link navigasi "Peminjaman Aset" di navbar admin (warna ungu)

### 📝 Testing Checklist

Untuk menguji sistem:

1. **Buat Peminjaman Baru:**
   - [ ] Akses `/asset-borrowing`
   - [ ] Isi semua data peminjam (termasuk alamat)
   - [ ] Pilih barang dan isi merk/tipe
   - [ ] Set kondisi barang (centang Baik & Lengkap)
   - [ ] Submit form

2. **Proses oleh Admin:**
   - [ ] Login sebagai admin
   - [ ] Klik menu "Peminjaman Aset" (ungu)
   - [ ] Klik "Detail" pada peminjaman yang baru dibuat
   - [ ] Isi data PIHAK PERTAMA sesuai contoh
   - [ ] Klik "Simpan & Generate Surat"
   - [ ] Verifikasi nomor surat muncul (format: XXX/SPB/UPKFEB/...)

3. **Verifikasi PDF:**
   - [ ] Klik "Preview PDF" - PDF terbuka di tab baru
   - [ ] Klik "Download PDF" - PDF terdownload
   - [ ] Buka PDF dan cek:
     - [ ] Logo FEB UNDIP muncul
     - [ ] Nomor surat benar
     - [ ] Data PIHAK PERTAMA lengkap
     - [ ] Data PIHAK KEDUA lengkap
     - [ ] Tabel barang dengan kondisi (x pada kolom Baik & Lengkap)
     - [ ] Format sesuai contoh surat

### 🔍 File Penting

**Backend:**
- `app/Services/BorrowingDocumentService.php` - Logic generate nomor & PDF
- `app/Http/Controllers/AssetBorrowingController.php` - Controller
- `app/Models/AssetBorrowing.php` - Model dengan field baru
- `app/Models/AssetBorrowingItem.php` - Model kondisi barang

**Frontend:**
- `resources/views/pdf/borrowing-document.blade.php` - Template PDF
- `resources/views/admin/asset-borrowing/index.blade.php` - List peminjaman
- `resources/views/admin/asset-borrowing/show.blade.php` - Detail & form admin

**Database:**
- `database/migrations/2026_02_10_103422_add_document_fields_to_asset_borrowings_table.php`
- `database/migrations/2026_02_10_103433_add_item_condition_to_asset_borrowing_items_table.php`

**Config:**
- `config/dompdf.php` - Konfigurasi PDF generator

**Documentation:**
- `BORROWING_DOCUMENT_SYSTEM.md` - Dokumentasi lengkap sistem

### 🎯 Next Steps

Sistem sudah siap digunakan! Untuk testing:

```bash
# Pastikan server berjalan
php artisan serve

# Akses aplikasi
http://localhost:8000
```

**Test URL:**
- Form User: `http://localhost:8000/asset-borrowing`
- Admin Panel: `http://localhost:8000/admin/asset-borrowings` (setelah login)

---

**Status: ✅ COMPLETED**

Semua fitur telah diimplementasikan sesuai dengan contoh surat peminjaman barang yang diberikan.
