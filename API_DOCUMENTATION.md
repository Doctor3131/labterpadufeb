# API Documentation - Booking System

## 📡 Public API Endpoints

### 1. Get Available Labs Based on Capacity

**Endpoint:** `POST /booking/available-labs`

**Description:** Returns list of labs that can accommodate the specified number of participants, sorted by capacity (smallest first).

**Request Body:**
```json
{
  "jumlah_peserta": 25
}
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "EL. 301",
    "capacity": 30,
    "description": "Laboratorium Komputer 1",
    "created_at": "2025-12-08T10:00:00Z",
    "updated_at": "2025-12-08T10:00:00Z"
  },
  {
    "id": 2,
    "name": "EL. 306",
    "capacity": 40,
    "description": "Laboratorium Komputer 2",
    "created_at": "2025-12-08T10:00:00Z",
    "updated_at": "2025-12-08T10:00:00Z"
  }
]
```

**Use Case:**
- Called when user inputs number of participants
- Frontend uses this to recommend suitable labs
- Auto-select the first lab (smallest capacity that fits)

---

### 2. Check Lab Availability

**Endpoint:** `POST /booking/check-availability`

**Description:** Checks if a specific lab is available at the requested date and time.

**Request Body:**
```json
{
  "lab_id": 1,
  "tanggal": "2025-12-15",
  "start_time": "13:00",
  "end_time": "15:00"
}
```

**Response (Available):**
```json
{
  "available": true,
  "message": "Lab tersedia"
}
```

**Response (Not Available):**
```json
{
  "available": false,
  "message": "Lab sudah terpakai pada jadwal tersebut"
}
```

**Logic:**
1. Convert date to day name (Senin, Selasa, etc.)
2. Check if there's any schedule with:
   - Same lab
   - Same day
   - Overlapping time range
3. Return availability status

**Use Case:**
- Called when user selects lab, date, or time
- Real-time feedback to prevent double booking
- Show green checkmark if available, red warning if not

---

### 3. Submit Booking Request

**Endpoint:** `POST /booking`

**Description:** Create a new booking request.

**Request Body (Form Data):**
```
booking_type: "non_perkuliahan"
nama_peminjam: "John Doe"
program_studi: "Ekonomi Pembangunan"
nim: "24030001"
no_telpon: "08123456789"
alamat: "Jl. Sudirman No. 123"
lab_id: 1
tanggal: "2025-12-15"
start_time: "13:00"
end_time: "15:00"
jumlah_peserta: 25
jenis_kegiatan: "Seminar"
nama_kegiatan: "Workshop SPSS"
jabatan: "Ketua Panitia"
kebutuhan_peralatan: "Proyektor, Mic"
document: [FILE] (PDF, max 5MB)
```

**Validation Rules:**
```php
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
    
    // Conditional validation
    'jenis_kegiatan' => 'required_if:booking_type,non_perkuliahan',
    'nama_kegiatan' => 'required_if:booking_type,non_perkuliahan',
    'mata_kuliah' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap',
    'dosen_pengampu' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap',
    'nip_dosen' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap',
]
```

**Response (Success):**
```
Redirect to: /booking/success/{booking_id}
```

**Response (Validation Error):**
```
Redirect back with errors
```

---

## 🔐 Admin API Endpoints (Future)

### 4. Get Pending Bookings

**Endpoint:** `GET /api/admin/bookings/pending`

**Auth:** Bearer Token (Asisten Lab)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "booking_type": "non_perkuliahan",
      "nama_peminjam": "John Doe",
      "program_studi": "Ekonomi Pembangunan",
      "nim": "24030001",
      "no_telpon": "08123456789",
      "jenis_kegiatan": "Seminar",
      "nama_kegiatan": "Workshop SPSS",
      "lab": {
        "id": 1,
        "name": "EL. 301",
        "capacity": 30
      },
      "tanggal": "2025-12-15",
      "start_time": "13:00",
      "end_time": "15:00",
      "jumlah_peserta": 25,
      "status": "pending",
      "created_at": "2025-12-08T10:00:00Z"
    }
  ],
  "meta": {
    "total": 5,
    "per_page": 15,
    "current_page": 1
  }
}
```

---

### 5. Approve Booking

**Endpoint:** `POST /api/admin/bookings/{id}/approve`

**Auth:** Bearer Token (Asisten Lab)

**Request Body:**
```json
{
  "admin_notes": "Disetujui untuk tanggal 15 Desember 2025"
}
```

**Response:**
```json
{
  "message": "Booking approved successfully",
  "data": {
    "id": 1,
    "status": "approved",
    "approved_by": 1,
    "approved_at": "2025-12-08T14:30:00Z",
    "admin_notes": "Disetujui untuk tanggal 15 Desember 2025"
  },
  "schedules_created": 1
}
```

**Logic:**
1. Update booking status to 'approved'
2. Set approved_by to current admin ID
3. Set approved_at to current timestamp
4. Save admin_notes
5. Create schedule entry/entries:
   - If `perkuliahan_tetap`: Generate 16 entries (1 semester)
   - Else: Generate 1 entry
6. Send notification to peminjam
7. Return success response

---

### 6. Reject Booking

**Endpoint:** `POST /api/admin/bookings/{id}/reject`

**Auth:** Bearer Token (Asisten Lab)

**Request Body:**
```json
{
  "admin_notes": "Lab sudah terpakai pada jam tersebut. Silakan ajukan ulang dengan jam berbeda."
}
```

**Response:**
```json
{
  "message": "Booking rejected successfully",
  "data": {
    "id": 1,
    "status": "rejected",
    "admin_notes": "Lab sudah terpakai pada jam tersebut. Silakan ajukan ulang dengan jam berbeda."
  }
}
```

**Logic:**
1. Update booking status to 'rejected'
2. Save admin_notes (reason for rejection)
3. Send notification to peminjam with rejection reason
4. Return success response

---

## 📊 Database Queries

### Get Available Labs Query
```php
Lab::where('capacity', '>=', $jumlahPeserta)
   ->orderBy('capacity', 'asc')
   ->get();
```

### Check Availability Query
```php
$isAvailable = !Schedule::where('lab_id', $labId)
    ->where('day', $dayName)
    ->where(function($query) use ($startTime, $endTime) {
        $query->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function($q) use ($startTime, $endTime) {
                  $q->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime);
              });
    })
    ->exists();
```

### Get Pending Bookings Query
```php
Booking::with(['lab', 'approver'])
    ->where('status', 'pending')
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

---

## 🔄 Webhook Events (Future)

### booking.created
```json
{
  "event": "booking.created",
  "timestamp": "2025-12-08T10:00:00Z",
  "data": {
    "booking_id": 1,
    "nama_peminjam": "John Doe",
    "booking_type": "non_perkuliahan",
    "lab_id": 1,
    "tanggal": "2025-12-15"
  }
}
```

### booking.approved
```json
{
  "event": "booking.approved",
  "timestamp": "2025-12-08T14:30:00Z",
  "data": {
    "booking_id": 1,
    "approved_by": 1,
    "schedules_created": 1
  }
}
```

### booking.rejected
```json
{
  "event": "booking.rejected",
  "timestamp": "2025-12-08T14:30:00Z",
  "data": {
    "booking_id": 1,
    "admin_notes": "Rejection reason"
  }
}
```

---

## 🛡️ Error Codes

### Client Errors (4xx)
```
400 Bad Request - Invalid input data
401 Unauthorized - Authentication required
403 Forbidden - Insufficient permissions
404 Not Found - Resource not found
422 Unprocessable Entity - Validation failed
```

### Server Errors (5xx)
```
500 Internal Server Error - Server error
503 Service Unavailable - Service temporarily down
```

### Custom Error Responses
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "jumlah_peserta": ["The jumlah peserta field is required."],
      "lab_id": ["The selected lab id is invalid."]
    }
  }
}
```

---

## 📝 Request Examples

### cURL Example: Check Availability
```bash
curl -X POST http://localhost:8000/booking/check-availability \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "lab_id": 1,
    "tanggal": "2025-12-15",
    "start_time": "13:00",
    "end_time": "15:00"
  }'
```

### JavaScript Example: Submit Booking
```javascript
const formData = new FormData();
formData.append('booking_type', 'non_perkuliahan');
formData.append('nama_peminjam', 'John Doe');
formData.append('program_studi', 'Ekonomi Pembangunan');
formData.append('nim', '24030001');
formData.append('no_telpon', '08123456789');
formData.append('lab_id', '1');
formData.append('tanggal', '2025-12-15');
formData.append('start_time', '13:00');
formData.append('end_time', '15:00');
formData.append('jumlah_peserta', '25');
formData.append('jenis_kegiatan', 'Seminar');
formData.append('nama_kegiatan', 'Workshop SPSS');
formData.append('document', fileInput.files[0]);

fetch('/booking', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: formData
})
.then(response => {
  if (response.redirected) {
    window.location.href = response.url;
  }
})
.catch(error => console.error('Error:', error));
```

---

## 🔧 Rate Limiting

### Public Endpoints
```
/booking/check-availability: 60 requests/minute
/booking/available-labs: 60 requests/minute
/booking (POST): 5 requests/minute
```

### Admin Endpoints
```
/api/admin/*: 120 requests/minute
```

---

## 📈 Monitoring & Logging

### Logged Events
```
- Booking created
- Booking approved
- Booking rejected
- Schedule created (via booking approval)
- File uploaded
- Validation errors
- API rate limit exceeded
```

### Log Format
```json
{
  "timestamp": "2025-12-08T10:00:00Z",
  "event": "booking.created",
  "user_ip": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "booking_id": 1,
  "data": {
    "nama_peminjam": "John Doe",
    "lab_id": 1,
    "tanggal": "2025-12-15"
  }
}
```

---

Semua endpoint sudah diimplementasi dan siap digunakan! 🚀
