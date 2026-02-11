# Dokumentasi Surat Peminjaman Barang

## Overview
Sistem ini menghasilkan Surat Peminjaman Barang yang sesuai dengan format resmi FEB UNDIP. Surat ini mencakup data PIHAK PERTAMA (Admin/Penanggung Jawab) dan PIHAK KEDUA (Peminjam) serta detail barang yang dipinjam dengan kondisinya.

## Fitur Utama

### 1. **Form Peminjaman User (PIHAK KEDUA)**
User mengisi form peminjaman melalui halaman publik `/asset-borrowing` dengan informasi:
- Data pribadi (nama, jabatan, alamat, telepon, email)
- Pilihan laboratorium
- Barang yang akan dipinjam dengan detail:
  - Nama barang
  - Merk/Tipe
  - Jumlah
  - Kondisi (Baik, Cukup, Lengkap)
  - Keterangan tambahan
- Tanggal pinjam dan kembali
- Tujuan peminjaman
- Dokumen pendukung (opsional)

### 2. **Form Admin Melengkapi Data (PIHAK PERTAMA)**
Setelah user submit, admin masuk ke `/admin/asset-borrowings` dan melengkapi:
- Nama penanggung jawab
- Jabatan (contoh: Asisten UPK)
- Alamat
- Telepon kantor
- Tanggal surat (opsional, default hari ini)

### 3. **Generate PDF Surat Peminjaman**
Setelah admin mengisi dan submit form PIHAK PERTAMA:
- Sistem generate nomor surat otomatis dengan format: `XXX/SPB/UPKFEB/ROMAN_MONTH/YEAR`
  - Contoh: `003/SPB/UPKFEB/IX/2025`
- PDF surat peminjaman dibuat dengan format resmi FEB UNDIP
- PDF disimpan di `storage/app/public/borrowing-documents/`

## Format Nomor Surat

Nomor surat digenerate otomatis dengan format:
```
XXX/SPB/UPKFEB/ROMAN_MONTH/YEAR
```

Dimana:
- `XXX`: Nomor urut peminjaman dalam bulan tersebut (3 digit dengan leading zero)
- `SPB`: Singkatan "Surat Peminjaman Barang"
- `UPKFEB`: Unit Pelaksana Komputer FEB
- `ROMAN_MONTH`: Bulan dalam angka romawi (I-XII)
- `YEAR`: Tahun 4 digit

Contoh: `003/SPB/UPKFEB/IX/2025` (Peminjaman ke-3 di bulan September 2025)

## Database Schema

### Field Baru di `asset_borrowings`:
```sql
- document_number (string, nullable) - Nomor surat peminjaman
- first_party_name (string, nullable) - Nama PIHAK PERTAMA
- first_party_position (string, nullable) - Jabatan PIHAK PERTAMA
- first_party_address (string, nullable) - Alamat PIHAK PERTAMA
- first_party_phone (string, nullable) - Telepon PIHAK PERTAMA
- borrower_address (string, nullable) - Alamat peminjam
- document_date (date, nullable) - Tanggal surat
- generated_document_path (string, nullable) - Path ke PDF yang digenerate
```

### Field Baru di `asset_borrowing_items`:
```sql
- brand_type (string, nullable) - Merk/Tipe barang
- condition_good (boolean, default true) - Kondisi Baik
- condition_adequate (boolean, default false) - Kondisi Cukup
- condition_complete (boolean, default true) - Kondisi Lengkap
- remarks (string, nullable) - Keterangan tambahan
```

## Routes

### Public Routes:
```php
GET  /asset-borrowing                    # Form peminjaman (user mengisi data PIHAK KEDUA)
POST /asset-borrowing                    # Submit peminjaman
GET  /asset-borrowing/success/{id}       # Halaman sukses
```

### Admin Routes:
```php
GET  /admin/asset-borrowings                    # List semua peminjaman
GET  /admin/asset-borrowings/{id}               # Detail & form PIHAK PERTAMA
POST /admin/asset-borrowings/{id}/first-party   # Update PIHAK PERTAMA & generate PDF
GET  /admin/asset-borrowings/{id}/preview       # Preview PDF di browser
GET  /admin/asset-borrowings/{id}/download      # Download PDF
POST /admin/asset-borrowings/{id}/approve       # Approve peminjaman
POST /admin/asset-borrowings/{id}/reject        # Reject peminjaman
```

## Workflow

### 1. User Mengajukan Peminjaman:
1. User mengakses `/asset-borrowing`
2. Mengisi data diri sebagai PIHAK KEDUA
3. Memilih lab dan barang yang akan dipinjam
4. Mengisi detail kondisi barang
5. Submit form
6. Status: `pending`

### 2. Admin Memproses:
1. Admin login dan akses `/admin/asset-borrowings`
2. Klik detail pada peminjaman yang `pending`
3. Review data peminjam dan barang
4. Mengisi data PIHAK PERTAMA (penanggung jawab)
5. Klik "Simpan & Generate Surat"
6. Sistem generate nomor surat dan PDF
7. Admin bisa preview atau download PDF
8. Admin approve/reject peminjaman

### 3. Generate Ulang:
Jika admin perlu update data PIHAK PERTAMA:
- Edit data di form yang sama
- Klik "Update & Generate Ulang Surat"
- Nomor surat tetap sama, tapi PDF dibuat ulang dengan data terbaru

## File Structure

```
app/
├── Services/
│   └── BorrowingDocumentService.php    # Service untuk generate nomor & PDF
├── Http/Controllers/
│   └── AssetBorrowingController.php    # Controller dengan method admin
├── Models/
│   ├── AssetBorrowing.php              # Model dengan field baru
│   └── AssetBorrowingItem.php          # Model dengan field kondisi

resources/views/
├── pdf/
│   └── borrowing-document.blade.php    # Template PDF surat peminjaman
└── admin/asset-borrowing/
    ├── index.blade.php                 # List peminjaman
    └── show.blade.php                  # Detail & form PIHAK PERTAMA

database/migrations/
├── 2026_02_10_103422_add_document_fields_to_asset_borrowings_table.php
└── 2026_02_10_103433_add_item_condition_to_asset_borrowing_items_table.php
```

## Template PDF

Template PDF mengikuti format contoh surat dengan:
- Header FEB UNDIP dengan logo
- Judul "SURAT PEMINJAMAN BARANG" dengan nomor surat
- Data PIHAK PERTAMA dan PIHAK KEDUA
- Tabel barang dengan kolom:
  - No
  - Nama Barang
  - Merk/Tipe
  - Jumlah
  - Kondisi (Baik, Cukup, Lengkap dengan checkbox)
  - Keterangan
- Footer dengan tempat & tanggal
- Ruang tanda tangan PIHAK KEDUA dan PIHAK PERTAMA

## Package Dependencies

```json
{
    "barryvdh/laravel-dompdf": "^3.1"
}
```

## Kustomisasi

### Ubah Format Nomor Surat:
Edit method `generateDocumentNumber()` di `BorrowingDocumentService.php`

### Ubah Template PDF:
Edit file `resources/views/pdf/borrowing-document.blade.php`

### Ubah Logo:
Ganti file `public/images/LogoUndips.png`

## Testing

### Test Flow Lengkap:
1. Buat peminjaman baru sebagai user
2. Login sebagai admin
3. Buka detail peminjaman
4. Isi data PIHAK PERTAMA:
   - Nama: Zola Gio Amri sakhi
   - Jabatan: Asisten UPK
   - Alamat: Semarang
   - Telepon: +62 877-4119-1305
5. Submit form
6. Verifikasi nomor surat tergenerate
7. Preview PDF
8. Download PDF
9. Cek PDF sesuai format contoh

## Troubleshooting

### PDF Tidak Tergenerate:
- Pastikan folder `storage/app/public/borrowing-documents/` bisa ditulis
- Jalankan: `php artisan storage:link`
- Check logs di `storage/logs/laravel.log`

### Nomor Surat Duplikat:
- Cek apakah ada data korup di database
- Verify query count di `generateDocumentNumber()`

### Logo Tidak Muncul di PDF:
- Pastikan file `public/images/LogoUndips.png` ada
- Gunakan `public_path()` bukan `asset()` di template PDF
