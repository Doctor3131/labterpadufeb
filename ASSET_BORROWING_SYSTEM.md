# Sistem Peminjaman Aset Laboratorium

## Overview
Sistem peminjaman aset laboratorium yang terintegrasi dengan sistem inventaris. Memungkinkan mahasiswa, dosen, dan staff untuk meminjam peralatan laboratorium dengan persetujuan admin.

## Fitur Utama

### 1. **Form Peminjaman Publik (Multi-Step)**
- **Step 1**: Data Peminjam (nama, status, kontak)
- **Step 2**: Pilih Lab & Aset yang akan dipinjam
- **Step 3**: Jadwal peminjaman & tujuan
- **Step 4**: Konfirmasi data

### 2. **Integrasi dengan Inventaris**
- Cek ketersediaan real-time berdasarkan:
  - **Structured Tag**: Pilih unit spesifik (PC, Laptop, dll)
  - **Seat Number**: Pilih nomor kursi/posisi
  - **Aggregate**: Input jumlah (Router, AC, dll)
- Hanya menampilkan aset dengan kondisi BAIK dan tersedia

### 3. **Tracking System**
- Setiap peminjaman mendapat kode tracking unik (format: AST-XXXXXX)
- User dapat melacak status via halaman tracking
- Timeline lengkap dari pengajuan hingga pengembalian

### 4. **Status Peminjaman**
- `pending` - Menunggu persetujuan admin
- `approved` - Disetujui, menunggu pengambilan
- `rejected` - Ditolak oleh admin
- `borrowed` - Sedang dipinjam
- `returned` - Sudah dikembalikan
- `overdue` - Terlambat mengembalikan
- `cancelled` - Dibatalkan

## Database Schema

### Table: `asset_borrowings`
```
- id
- borrower_name
- borrower_type (Mahasiswa/Dosen/Staff/Lainnya)
- borrower_id_number (NIM/NIP)
- study_program, class_year, position
- phone_number, email
- lab_id (FK to labs)
- purpose
- borrow_date, return_date
- borrow_time, return_time
- status
- tracking_token (unique)
- approval & handover timestamps
- condition notes
- document_path
```

### Table: `asset_borrowing_items`
```
- id
- asset_borrowing_id (FK)
- item_id (FK to items)
- asset_unit_id (FK to asset_units) - untuk Structured Tag & Seat Number
- inventory_balance_id (FK) - untuk Aggregate
- quantity (untuk Aggregate)
- notes
```

## Routes

### Public Routes
```php
GET  /asset-borrowing                    # Form peminjaman
POST /asset-borrowing                    # Submit peminjaman
GET  /asset-borrowing/success/{token}    # Halaman sukses
GET  /asset-borrowing/track              # Tracking peminjaman
GET  /asset-borrowing/available-assets   # API cek ketersediaan
```

### Admin Routes (akan dibuat berikutnya)
```php
GET  /admin/asset-borrowings             # Daftar peminjaman
GET  /admin/asset-borrowings/{id}        # Detail peminjaman
POST /admin/asset-borrowings/{id}/approve # Setujui
POST /admin/asset-borrowings/{id}/reject  # Tolak
POST /admin/asset-borrowings/{id}/handout # Serahkan barang
POST /admin/asset-borrowings/{id}/receive # Terima kembali
```

## File Structure

```
app/
├── Models/
│   ├── AssetBorrowing.php
│   └── AssetBorrowingItem.php
├── Http/Controllers/
│   └── AssetBorrowingController.php

resources/views/asset-borrowing/
├── create.blade.php      # Form peminjaman (4 steps)
├── success.blade.php     # Halaman sukses
└── track.blade.php       # Tracking status

database/migrations/
├── 2026_02_06_000001_create_asset_borrowings_table.php
└── 2026_02_06_000002_create_asset_borrowing_items_table.php
```

## Cara Penggunaan

### User Flow
1. User klik "Pinjam Aset" di landing page
2. Isi data peminjam (Step 1)
3. Pilih lab dan aset yang ingin dipinjam (Step 2)
   - Sistem otomatis cek ketersediaan
   - Untuk Structured Tag/Seat: pilih unit spesifik
   - Untuk Aggregate: input jumlah
4. Tentukan jadwal pinjam & kembali (Step 3)
5. Konfirmasi & submit (Step 4)
6. Dapatkan kode tracking untuk monitoring

### Admin Flow (Next Phase)
1. Admin melihat daftar pengajuan di dashboard
2. Review detail peminjaman
3. Approve/Reject dengan catatan
4. Catat kondisi barang saat diserahkan
5. Catat kondisi barang saat dikembalikan
6. Update status menjadi "returned"

## Integration Points

### Dengan Sistem Inventaris
- Menggunakan relasi ke `items`, `asset_units`, `inventory_balances`
- Real-time check availability
- Filter hanya aset dengan `is_borrowable = true`
- Filter kondisi = BAIK dan is_available = true

### Dengan Sistem User
- Tracking siapa yang approve, handout, receive
- Relasi ke tabel `users` untuk admin actions

## Next Steps

1. **Admin Management Panel**
   - Dashboard untuk kelola peminjaman
   - Approve/reject functionality
   - Handout & receive tracking
   - Report peminjaman

2. **Notifications**
   - Email/SMS notification saat status berubah
   - Reminder untuk pengembalian

3. **Analytics**
   - Statistik aset paling sering dipinjam
   - Tracking utilization rate
   - Late return reports

4. **Mobile Optimization**
   - PWA support
   - QR code scanning untuk asset tag

## API Endpoints

### Get Available Assets
```
GET /asset-borrowing/available-assets?lab_id={id}&item_id={id}

Response (Structured Tag/Seat):
{
  "type": "structured",
  "units": [
    {"id": 1, "asset_tag": "LAB1-H3-001", "subtype": "ADMIN"},
    {"id": 2, "asset_tag": "LAB1-H3-002", "subtype": null}
  ]
}

Response (Aggregate):
{
  "type": "aggregate",
  "available_quantity": 15
}
```

## Security Features
- Rate limiting (10 submissions per minute)
- File upload validation (PDF/JPG/PNG, max 5MB)
- CSRF protection
- Unique tracking token
- Input validation & sanitization

## Status Badge Colors
- Pending: Yellow
- Approved: Blue
- Rejected: Red
- Borrowed: Purple
- Returned: Green
- Overdue: Red
- Cancelled: Gray
