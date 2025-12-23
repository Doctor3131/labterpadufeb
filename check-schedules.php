<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Schedule;

echo "Checking schedules with student_count...\n\n";

$schedules = Schedule::with('lab')->take(10)->get();

foreach ($schedules as $schedule) {
    echo "ID: {$schedule->id}\n";
    echo "Lab: {$schedule->lab->name}\n";
    echo "Course: {$schedule->course}\n";
    echo "Komting: " . ($schedule->komting ?? 'N/A') . "\n";
    echo "Student Count: " . ($schedule->student_count ?? 'N/A') . "\n";
    echo "---\n\n";
}

echo "Total schedules: " . Schedule::count() . "\n";
echo "With student_count: " . Schedule::whereNotNull('student_count')->count() . "\n";
