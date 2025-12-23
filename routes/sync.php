<?php

use Illuminate\Support\Facades\Route;
use App\Models\Booking;
use App\Models\Schedule;

Route::get('/sync-bookings', function () {
    $approvedBookings = Booking::where('status', 'approved')
        ->whereDoesntHave('schedule')
        ->get();

    $synced = [];
    foreach ($approvedBookings as $booking) {
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
