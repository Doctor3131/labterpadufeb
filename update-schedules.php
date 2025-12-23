<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Schedule;

echo "Updating schedules with student_count and komting...\n\n";

// Update schedules that have booking_id but missing student_count/komting
$schedules = Schedule::whereNotNull('booking_id')
    ->with('booking')
    ->get();

$updated = 0;
foreach ($schedules as $schedule) {
    if ($schedule->booking) {
        $schedule->update([
            'komting' => $schedule->booking->nama_peminjam,
            'student_count' => $schedule->booking->jumlah_peserta,
        ]);
        echo "✓ Updated schedule #{$schedule->id} - {$schedule->course} ({$schedule->booking->jumlah_peserta} peserta)\n";
        $updated++;
    }
}

echo "\n✅ Update completed! Total updated: {$updated}\n";
