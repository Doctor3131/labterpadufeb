<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Schedule;

echo "Syncing approved bookings to schedules...\n\n";

$approvedBookings = Booking::where('status', 'approved')
    ->whereDoesntHave('schedule')
    ->get();

echo "Found {$approvedBookings->count()} approved bookings without schedule entries.\n\n";

foreach ($approvedBookings as $booking) {
    $scheduleData = [
        'lab_id' => $booking->lab_id,
        'day' => $booking->day,
        'start_time' => $booking->start_time,
        'end_time' => $booking->end_time,
        'booking_id' => $booking->id,
    ];
    
    if ($booking->is_recurring) {
        $scheduleData['type'] = 'booking_recurring';
        $scheduleData['start_date'] = $booking->tanggal;
        $scheduleData['end_date'] = null;
        $scheduleData['course'] = $booking->mata_kuliah;
        $scheduleData['lecturer'] = $booking->dosen_pengampu;
    } else {
        $scheduleData['type'] = 'booking_onetime';
        $scheduleData['start_date'] = $booking->tanggal;
        $scheduleData['end_date'] = $booking->tanggal;
        
        if ($booking->booking_type === 'non_perkuliahan') {
            $scheduleData['course'] = $booking->nama_kegiatan;
            $scheduleData['lecturer'] = $booking->nama_peminjam;
        } else {
            $scheduleData['course'] = $booking->mata_kuliah;
            $scheduleData['lecturer'] = $booking->dosen_pengampu;
        }
    }
    
    $schedule = Schedule::create($scheduleData);
    echo "✓ Created schedule #{$schedule->id} for booking #{$booking->id} ({$booking->nama_kegiatan ?? $booking->mata_kuliah})\n";
}

echo "\n✅ Sync completed! Total synced: {$approvedBookings->count()}\n";
