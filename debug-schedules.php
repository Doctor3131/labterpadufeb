<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Schedule;

echo "=== DEBUG SCHEDULES ===\n\n";

// 1. Check approved bookings
$approvedBookings = Booking::where('status', 'approved')->get();
echo "1. Approved Bookings: " . $approvedBookings->count() . "\n";
foreach ($approvedBookings as $booking) {
    $name = $booking->nama_kegiatan ? $booking->nama_kegiatan : $booking->mata_kuliah;
    echo "   - Booking #{$booking->id}: {$name}\n";
    echo "     Lab: {$booking->lab->name}, Date: {$booking->tanggal}, Day: {$booking->day}\n";
    $hasSchedule = $booking->schedule ? "Yes (#{$booking->schedule->id})" : "No";
    echo "     Has Schedule: {$hasSchedule}\n\n";
}

// 2. Check all schedules
$allSchedules = Schedule::with('lab')->get();
echo "\n2. Total Schedules: " . $allSchedules->count() . "\n";
echo "   Regular schedules: " . Schedule::where('type', 'regular')->count() . "\n";
echo "   Booking recurring: " . Schedule::where('type', 'booking_recurring')->count() . "\n";
echo "   Booking onetime: " . Schedule::where('type', 'booking_onetime')->count() . "\n\n";

// 3. Show schedule details
echo "3. Schedule Details:\n";
foreach ($allSchedules as $schedule) {
    echo "   Schedule #{$schedule->id}:\n";
    echo "     Type: {$schedule->type}\n";
    echo "     Lab: {$schedule->lab->name}\n";
    echo "     Day: {$schedule->day}\n";
    echo "     Time: {$schedule->start_time} - {$schedule->end_time}\n";
    echo "     Course: {$schedule->course}\n";
    echo "     Date Range: {$schedule->start_date} to {$schedule->end_date}\n";
    echo "     Booking ID: {$schedule->booking_id}\n\n";
}

// 4. Test current week query (like LandingController)
$startOfWeek = \Carbon\Carbon::now('Asia/Jakarta')->startOfWeek(\Carbon\Carbon::MONDAY);
$endOfWeek = $startOfWeek->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

echo "4. Current Week Test:\n";
echo "   Start: {$startOfWeek->format('Y-m-d')} ({$startOfWeek->format('l')})\n";
echo "   End: {$endOfWeek->format('Y-m-d')} ({$endOfWeek->format('l')})\n\n";

$weekSchedules = Schedule::where(function ($query) use ($startOfWeek, $endOfWeek) {
    $query->whereNull('start_date')
          ->orWhere(function ($q) use ($startOfWeek, $endOfWeek) {
              $q->where('start_date', '<=', $endOfWeek->format('Y-m-d'))
                ->where(function ($q2) use ($startOfWeek) {
                    $q2->whereNull('end_date')
                       ->orWhere('end_date', '>=', $startOfWeek->format('Y-m-d'));
                });
          });
})->get();

echo "   Schedules matching current week: " . $weekSchedules->count() . "\n";
foreach ($weekSchedules as $schedule) {
    echo "     - {$schedule->course} ({$schedule->day})\n";
}

// 5. Check bookings for this week
$weekBookings = Booking::whereBetween('tanggal', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
    ->where('status', 'approved')
    ->get();
echo "\n5. Approved bookings in current week: " . $weekBookings->count() . "\n";
foreach ($weekBookings as $booking) {
    $name = $booking->nama_kegiatan ? $booking->nama_kegiatan : $booking->mata_kuliah;
    echo "     - {$name} on {$booking->tanggal}\n";
}

echo "\n=== END DEBUG ===\n";
