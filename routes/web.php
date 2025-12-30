<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Models\Booking;
use App\Models\Schedule;

// Public Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Booking Routes (Public - No Authentication Required)
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/success/{token}', [BookingController::class, 'success'])->name('booking.success');
Route::post('/booking/available-labs', [BookingController::class, 'getAvailableLabs'])->name('booking.available-labs');

// Lab Availability API
Route::get('/api/labs/available', [App\Http\Controllers\LabController::class, 'checkAvailability'])->name('api.labs.available');

// PDF Print (for re-download)
Route::get('/booking/print/{token}', [BookingController::class, 'print'])->name('booking.print');

// Schedule Routes (Public)
Route::get('/schedules', function () {
    return view('schedules.index');
})->name('schedules.index');
Route::get('/schedules/week', [App\Http\Controllers\ScheduleController::class, 'getWeekSchedules'])->name('schedules.week');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Admin/Super Admin only)
Route::middleware('auth')->group(function () {
    // Redirect /dashboard to admin dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin Booking Management
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/bookings/{id}', [AdminController::class, 'show'])->name('admin.booking.show');
    Route::post('/admin/bookings/{id}/approve', [AdminController::class, 'approve'])->name('admin.booking.approve');
    Route::post('/admin/bookings/{id}/reject', [AdminController::class, 'reject'])->name('admin.booking.reject');
});

// Super Admin Only Routes
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/admin/users/create', [AuthController::class, 'showRegister'])->name('admin.users.create');
    Route::post('/admin/users', [AuthController::class, 'register'])->name('admin.users.store');
});

// Temporary Sync Route (Remove after use)
Route::get('/sync-bookings', function () {
    $approvedBookings = Booking::where('status', 'approved')
        ->whereDoesntHave('schedule')
        ->get();

    $synced = [];
    foreach ($approvedBookings as $booking) {
        // Use Carbon with Asia/Jakarta timezone for correct date
        $bookingDate = \Carbon\Carbon::parse($booking->tanggal)->timezone('Asia/Jakarta');
        
        $scheduleData = [
            'lab_id' => $booking->lab_id,
            'day' => $booking->day, // Use day from booking (already correct)
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'booking_id' => $booking->id,
            'komting' => $booking->nama_peminjam,
            'student_count' => $booking->jumlah_peserta,
        ];
        
        if ($booking->is_recurring) {
            $scheduleData['type'] = 'booking_recurring';
            $scheduleData['start_date'] = $bookingDate->toDateString();
            $scheduleData['end_date'] = null;
            $scheduleData['course'] = $booking->mata_kuliah;
            $scheduleData['lecturer'] = $booking->dosen_pengampu;
        } else {
            $scheduleData['type'] = 'booking_onetime';
            $scheduleData['start_date'] = $bookingDate->toDateString();
            $scheduleData['end_date'] = $bookingDate->toDateString();
            
            if ($booking->booking_type === 'non_perkuliahan') {
                $scheduleData['course'] = $booking->nama_kegiatan;
                $scheduleData['lecturer'] = $booking->nama_peminjam;
            } else {
                $scheduleData['course'] = $booking->mata_kuliah;
                $scheduleData['lecturer'] = $booking->dosen_pengampu;
            }
        }
        
        $schedule = Schedule::create($scheduleData);
        $synced[] = [
            'booking_id' => $booking->id,
            'schedule_id' => $schedule->id,
            'course' => $scheduleData['course'],
        ];
    }

    return response()->json([
        'message' => 'Sync completed!',
        'total' => count($synced),
        'synced' => $synced
    ]);
});

// Debug Route
Route::get('/debug-schedules', function () {
    $data = [];
    
    // 1. Check approved bookings
    $approvedBookings = Booking::with(['lab', 'schedule'])->where('status', 'approved')->get();
    $data['approved_bookings'] = [
        'count' => $approvedBookings->count(),
        'items' => $approvedBookings->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->nama_kegiatan ?: $b->mata_kuliah,
            'lab' => $b->lab->name,
            'date' => $b->tanggal->format('Y-m-d'),
            'day' => $b->day,
            'has_schedule' => $b->schedule ? $b->schedule->id : null,
        ])
    ];
    
    // 2. Check all schedules
    $allSchedules = Schedule::with('lab')->get();
    $data['all_schedules'] = [
        'count' => $allSchedules->count(),
        'regular' => Schedule::where('type', 'regular')->count(),
        'booking_recurring' => Schedule::where('type', 'booking_recurring')->count(),
        'booking_onetime' => Schedule::where('type', 'booking_onetime')->count(),
        'items' => $allSchedules->map(fn($s) => [
            'id' => $s->id,
            'type' => $s->type,
            'lab' => $s->lab->name,
            'day' => $s->day,
            'course' => $s->course,
            'start_date' => $s->start_date,
            'end_date' => $s->end_date,
            'booking_id' => $s->booking_id,
        ])
    ];
    
    // 3. Current week test
    $startOfWeek = \Carbon\Carbon::now('Asia/Jakarta')->startOfWeek(\Carbon\Carbon::MONDAY);
    $endOfWeek = $startOfWeek->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
    
    $data['current_week'] = [
        'start' => $startOfWeek->format('Y-m-d'),
        'end' => $endOfWeek->format('Y-m-d'),
    ];
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
});

// Fix Route - Delete wrong schedules and re-sync
Route::get('/fix-schedules', function () {
    // Delete schedules yang salah (booking_onetime dengan booking_id)
    $deleted = Schedule::whereIn('type', ['booking_onetime', 'booking_recurring'])
        ->whereNotNull('booking_id')
        ->delete();
    
    // Re-sync dengan data yang benar
    $approvedBookings = Booking::where('status', 'approved')->get();
    
    $synced = [];
    foreach ($approvedBookings as $booking) {
        $bookingDate = \Carbon\Carbon::parse($booking->tanggal)->timezone('Asia/Jakarta');
        
        $scheduleData = [
            'lab_id' => $booking->lab_id,
            'day' => $booking->day,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'booking_id' => $booking->id,
            'komting' => $booking->nama_peminjam,
            'student_count' => $booking->jumlah_peserta,
        ];
        
        if ($booking->is_recurring) {
            $scheduleData['type'] = 'booking_recurring';
            $scheduleData['start_date'] = $bookingDate->toDateString();
            $scheduleData['end_date'] = null;
            $scheduleData['course'] = $booking->mata_kuliah;
            $scheduleData['lecturer'] = $booking->dosen_pengampu;
        } else {
            $scheduleData['type'] = 'booking_onetime';
            $scheduleData['start_date'] = $bookingDate->toDateString();
            $scheduleData['end_date'] = $bookingDate->toDateString();
            
            if ($booking->booking_type === 'non_perkuliahan') {
                $scheduleData['course'] = $booking->nama_kegiatan;
                $scheduleData['lecturer'] = $booking->nama_peminjam;
            } else {
                $scheduleData['course'] = $booking->mata_kuliah;
                $scheduleData['lecturer'] = $booking->dosen_pengampu;
            }
        }
        
        $schedule = Schedule::create($scheduleData);
        $synced[] = [
            'booking_id' => $booking->id,
            'schedule_id' => $schedule->id,
            'day' => $scheduleData['day'],
            'date' => $scheduleData['start_date'],
            'course' => $scheduleData['course'],
        ];
    }
    
    return response()->json([
        'message' => 'Fix completed!',
        'deleted' => $deleted,
        'resynced' => count($synced),
        'schedules' => $synced
    ], 200, [], JSON_PRETTY_PRINT);
});
