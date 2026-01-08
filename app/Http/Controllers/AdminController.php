<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Show admin dashboard with pending bookings
     */
    public function dashboard()
    {
        $pendingBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'approved')
            ->orderBy('handled_at', 'desc')
            ->get();

        $rejectedBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.dashboard', compact('pendingBookings', 'approvedBookings', 'rejectedBookings'));
    }

    /**
     * Show booking detail
     */
    public function show($id)
    {
        $booking = Booking::with('lab')->findOrFail($id);
        return view('admin.booking-detail', compact('booking'));
    }

    /**
     * Approve booking
     */
    public function approve($id)
    {
        $booking = Booking::findOrFail($id);
        
        DB::transaction(function () use ($booking) {
            // Update booking status
            $updateData = [
                'status' => 'approved',
                'handled_at' => now()
            ];
            
            if (Auth::check()) {
                $updateData['handled_by'] = Auth::id();
            }
            
            $booking->update($updateData);

            // Create schedule entry from approved booking
            // Important: Use Carbon with Asia/Jakarta timezone to get correct day
            $bookingDate = \Carbon\Carbon::parse($booking->booking_date)->timezone('Asia/Jakarta');
            
            $scheduleData = [
                'lab_id' => $booking->lab_id,
                'day' => $booking->day, // Use day from booking (already correct)
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'booking_id' => $booking->id,
                'student_count' => $booking->participant_count,
            ];

            // Tentukan type dan data spesifik berdasarkan booking_type
            if ($booking->is_recurring) {
                // Perkuliahan tetap - recurring schedule
                $scheduleData['type'] = 'perkuliahan_tetap';
                $scheduleData['start_date'] = $bookingDate->toDateString();
                $scheduleData['end_date'] = null; // Recurring tanpa batas
                $scheduleData['course'] = $booking->course_name;
                $scheduleData['lecturer'] = $booking->lecturer_name;
                $scheduleData['komting'] = $booking->pic_name;
            } else {
                // One-time booking - use booking_type directly
                $scheduleData['type'] = $booking->booking_type; // perkuliahan_tidak_tetap, non_perkuliahan, or pribadi
                $scheduleData['start_date'] = $bookingDate->toDateString();
                $scheduleData['end_date'] = $bookingDate->toDateString();
                
                // Map data based on booking type
                if ($booking->booking_type === 'perkuliahan_tidak_tetap') {
                    $scheduleData['course'] = $booking->course_name;
                    $scheduleData['lecturer'] = $booking->lecturer_name;
                    $scheduleData['komting'] = $booking->pic_name;
                } elseif ($booking->booking_type === 'non_perkuliahan') {
                    $scheduleData['course'] = $booking->activity_name;
                    $scheduleData['lecturer'] = null;
                    $scheduleData['komting'] = null;
                } elseif ($booking->booking_type === 'pribadi') {
                    $scheduleData['course'] = $booking->purpose ?? 'Peminjaman Pribadi';
                    $scheduleData['lecturer'] = null;
                    $scheduleData['komting'] = null;
                } else {
                    // Fallback for regular type
                    $scheduleData['course'] = $booking->course_name ?? $booking->activity_name ?? $booking->purpose ?? 'Peminjaman';
                    $scheduleData['lecturer'] = null;
                    $scheduleData['komting'] = null;
                }
            }

            Schedule::create($scheduleData);
        });



        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil disetujui!');
    }

    /**
     * Reject booking
     */
    public function reject(Request $request, $id)
    {
        Log::info('Reject booking called', [
            'booking_id' => $id,
            'reason' => $request->rejection_reason
        ]);

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $booking = Booking::findOrFail($id);
        
        Log::info('Booking found', [
            'booking_id' => $booking->id,
            'current_status' => $booking->status
        ]);
        
        DB::transaction(function () use ($booking, $request) {
            // Delete related schedule if exists
            if ($booking->schedule) {
                $booking->schedule->delete();
            }
            
            // Update booking status
            $updateData = [
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'handled_at' => now()
            ];
            
            if (Auth::check()) {
                $updateData['handled_by'] = Auth::id();
            }
            
            $booking->update($updateData);
            
            Log::info('Booking rejected successfully', [
                'booking_id' => $booking->id,
                'new_status' => $booking->status
            ]);
        });

        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil ditolak.');
    }
}
