# User Flow - Sistem Peminjaman Lab

## 🎯 Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                      LANDING PAGE                               │
│  - Lihat jadwal mingguan (Senin-Sabtu)                         │
│  - Tombol "Ajukan Peminjaman"                                  │
│  - Tombol "Login Asisten Lab"                                  │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ├─────► Login Asisten Lab ──► Dashboard Admin
                           │                              (Future)
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    FORM PEMINJAMAN                              │
│  Step 1: Pilih Tipe Peminjaman                                 │
│          □ Perkuliahan Tetap                                   │
│          □ Perkuliahan Tidak Tetap                             │
│          □ Non-Perkuliahan                                     │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  Step 2: Data Peminjam                                         │
│          - Nama Lengkap                                        │
│          - Program Studi                                       │
│          - NIM                                                 │
│          - No. Telepon                                         │
│          - Alamat Tempat Tinggal                              │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  Step 3: Detail Kegiatan (Conditional)                        │
│                                                                │
│  IF Perkuliahan:                  IF Non-Perkuliahan:        │
│  - Mata Kuliah                    - Jenis Kegiatan           │
│  - Dosen Pengampu                 - Nama Kegiatan            │
│  - NIP Dosen                      - Jabatan                  │
│  - Software (optional)            - Kebutuhan Peralatan      │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  Step 4: Jadwal & Lab                                          │
│          - Input Jumlah Peserta                                │
│            → Smart Lab Recommendation ✓                        │
│          - Pilih Lab                                           │
│          - Pilih Tanggal                                       │
│          - Pilih Jam Mulai & Selesai                          │
│            → Real-time Availability Check ⏱                   │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  Step 5: Upload Dokumen                                        │
│          IF Perkuliahan: Scan KTM Koordinator MK              │
│          IF Non-Perkuliahan: Dokumen Pendukung                │
│          (PDF, max 5MB)                                        │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
                    [Submit Button]
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    VALIDATION                                   │
│  - Check all required fields                                   │
│  - Validate file format & size                                 │
│  - Check lab availability                                      │
│  - Save to database (status: pending)                          │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    SUCCESS PAGE                                 │
│  ✓ Booking ID                                                  │
│  ✓ Detail peminjaman                                           │
│  ✓ Status: Menunggu Persetujuan                               │
│  ✓ Informasi: Akan dihubungi via telepon                      │
│                                                                │
│  [Kembali ke Beranda]  [Ajukan Peminjaman Lagi]              │
└─────────────────────────────────────────────────────────────────┘
```

---

## 👨‍💼 Admin Flow (Asisten Lab)

```
┌─────────────────────────────────────────────────────────────────┐
│                      LOGIN PAGE                                 │
│  - Username (Email)                                            │
│  - Password                                                    │
│  - [Login Asisten Lab]                                         │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                 ADMIN DASHBOARD (Future)                        │
│                                                                │
│  Tab: Pending Bookings                                         │
│  ┌───────────────────────────────────────────────────────┐   │
│  │ #00001 | John Doe | Seminar | EL. 301 | 15 Des 2025  │   │
│  │        [View Detail] [Approve] [Reject]               │   │
│  └───────────────────────────────────────────────────────┘   │
│  ┌───────────────────────────────────────────────────────┐   │
│  │ #00002 | Jane Doe | Statistika | EL. 306 | 16 Des    │   │
│  │        [View Detail] [Approve] [Reject]               │   │
│  └───────────────────────────────────────────────────────┘   │
│                                                                │
│  Tab: Approved Bookings                                        │
│  Tab: Rejected Bookings                                        │
│  Tab: Manage Schedules                                         │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
               [Click "View Detail"]
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│               BOOKING DETAIL MODAL                              │
│                                                                │
│  Data Peminjam:                                                │
│  - Nama: John Doe                                             │
│  - NIM: 24030001                                              │
│  - Prodi: Ekonomi Pembangunan                                 │
│  - Telp: 08123456789                                          │
│                                                                │
│  Detail Kegiatan:                                              │
│  - Jenis: Seminar                                             │
│  - Nama: Workshop SPSS                                        │
│  - Jabatan: Ketua Panitia                                     │
│                                                                │
│  Jadwal:                                                       │
│  - Lab: EL. 301                                               │
│  - Tanggal: Senin, 15 Desember 2025                          │
│  - Jam: 13:00 - 15:00                                         │
│  - Peserta: 25 orang                                          │
│                                                                │
│  Dokumen: [Download PDF]                                       │
│                                                                │
│  Admin Notes:                                                  │
│  [Text area untuk catatan]                                    │
│                                                                │
│  [Approve Button] [Reject Button] [Close]                     │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ├─────► [Approve] ─────┐
                           │                       │
                           └─────► [Reject] ──────┤
                                                   │
                                                   ▼
┌─────────────────────────────────────────────────────────────────┐
│                APPROVAL/REJECTION PROCESS                       │
│                                                                │
│  IF Approved:                                                  │
│  1. Update booking.status = 'approved'                         │
│  2. Set booking.approved_by = current_admin_id                │
│  3. Set booking.approved_at = now()                           │
│  4. Save admin_notes                                           │
│  5. Create Schedule Entry/Entries:                            │
│     IF perkuliahan_tetap:                                     │
│        → Generate 16 schedule entries (1 semester)            │
│     ELSE:                                                      │
│        → Generate 1 schedule entry                            │
│  6. Send notification to peminjam                             │
│                                                                │
│  IF Rejected:                                                  │
│  1. Update booking.status = 'rejected'                         │
│  2. Save admin_notes (reason for rejection)                   │
│  3. Send notification to peminjam                             │
└─────────────────────────────────────────────────────────────────┘
                           │
                           ▼
                  [Back to Dashboard]
```

---

## 🔄 State Transitions

```
Booking States:
┌──────────┐
│ PENDING  │ ← Initial state when booking created
└──────────┘
     │
     ├────► [Admin Approve] ────► ┌──────────┐
     │                             │ APPROVED │ → Schedule created
     │                             └──────────┘
     │
     └────► [Admin Reject] ─────► ┌──────────┐
                                   │ REJECTED │ → Notification sent
                                   └──────────┘
```

---

## 📱 Responsive Behavior

### Mobile View
```
Landing Page:
┌────────────────┐
│   Lab FEB      │ [Login Asisten Lab]
├────────────────┤
│  Hero Section  │
│  with CTA      │
│ [Ajukan        │
│  Peminjaman]   │
├────────────────┤
│ ┌────────────┐ │
│ │Senin│Selasa│ │ ← Horizontal scroll tabs
│ └────────────┘ │
│                │
│ Schedule Table │ ← Horizontal scroll
│                │
└────────────────┘

Form Peminjaman:
┌────────────────┐
│  Tipe          │
│  Peminjaman    │
│  □ Perkuliahan │
│  □ Non-Perkul. │
├────────────────┤
│  Data Peminjam │
│  [Full width]  │
├────────────────┤
│  Detail        │
│  [Full width]  │
├────────────────┤
│ [Submit]       │
└────────────────┘
```

### Desktop View
```
Landing Page:
┌──────────────────────────────────────────────────┐
│ LabFEB      [Ajukan Peminjaman]   [Login Aslab] │
├──────────────────────────────────────────────────┤
│              Hero Section                        │
│       [Ajukan Peminjaman Button]                │
├──────────────────────────────────────────────────┤
│ [Senin][Selasa][Rabu][Kamis][Jumat][Sabtu]     │
│                                                  │
│          Full Width Schedule Table               │
│                                                  │
└──────────────────────────────────────────────────┘

Form Peminjaman:
┌──────────────────────────────────────────────────┐
│      Tipe Peminjaman (3 columns grid)           │
│  [Perkuliahan Tetap] [Tidak Tetap] [Non-Perkul]│
├──────────────────────────────────────────────────┤
│           Data Peminjam (2 columns)             │
│  [Nama]              [Program Studi]            │
│  [NIM]               [No. Telepon]              │
├──────────────────────────────────────────────────┤
│           Detail (2 columns if applicable)       │
├──────────────────────────────────────────────────┤
│  [Kembali]                          [Submit]    │
└──────────────────────────────────────────────────┘
```

---

## 🎨 Color Coding

### Status Colors
```
Pending:   🟡 Yellow   (bg-yellow-100, text-yellow-800)
Approved:  🟢 Green    (bg-green-100, text-green-800)
Rejected:  🔴 Red      (bg-red-100, text-red-800)

Available:    ✓ Green  (bg-green-50, border-green-200)
Not Available: ⚠ Red   (bg-red-50, border-red-200)

Recommended:  ✓ Green  (text-green-600)
Over Capacity: ⚠ Red   (text-red-600)
```

### Brand Colors
```
Primary:   Yellow-500  (#EAB308) → FEB UNDIP identity
Secondary: Gray-700    (#374151)
Accent:    Yellow-600  (#CA8A04) → Hover states
```

---

## ⚡ JavaScript Interactions

### Form Behavior
```javascript
// On booking type change
booking_type.onChange(() => {
  if (perkuliahan) {
    show(perkuliahanFields)
    hide(nonPerkuliahanFields)
    documentLabel = "Scan KTM Koordinator MK"
  } else {
    hide(perkuliahanFields)
    show(nonPerkuliahanFields)
    documentLabel = "Dokumen Pendukung"
  }
})

// On jumlah peserta input
jumlahPeserta.onInput(() => {
  const suitable = labs.filter(lab => lab.capacity >= peserta)
                       .sort((a,b) => a.capacity - b.capacity)
  
  if (suitable.length > 0) {
    labSelect.value = suitable[0].id
    showRecommendation(suitable[0].name)
  } else {
    showWarning("Over capacity")
  }
})

// On schedule change
[lab, tanggal, startTime, endTime].onChange(() => {
  checkAvailability()
    .then(response => {
      if (available) {
        showSuccess("Lab tersedia")
      } else {
        showError("Lab sudah terpakai")
      }
    })
})
```

---

Semua flow ini sudah diimplementasi dalam sistem! 🎉
