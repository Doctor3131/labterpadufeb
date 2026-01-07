<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $booking->update([
                'status' => 'approved',
                'handled_by' => auth()->id(),
                'handled_at' => now()
            ]);

            // Create schedule entry from approved booking
            // Important: Use Carbon with Asia/Jakarta timezone to get correct day
            $bookingDate = \Carbon\Carbon::parse($booking->booking_date)->timezone('Asia/Jakarta');
            
            $scheduleData = [
                'lab_id' => $booking->lab_id,
                'day' => $booking->day, // Use day from booking (already correct)
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'booking_id' => $booking->id,
                // Linked data - no need to copy text fields thanks to smart accessors
            ];

            // Tentukan type berdasarkan booking_type
            if ($booking->is_recurring) {
                // Perkuliahan tetap - recurring schedule
                $scheduleData['type'] = 'perkuliahan_tetap';
                $scheduleData['start_date'] = $bookingDate->toDateString();
                $scheduleData['end_date'] = null; // Recurring tanpa batas
            } else {
                // One-time booking - use booking_type directly
                $scheduleData['type'] = $booking->booking_type; // perkuliahan_tidak_tetap, non_perkuliahan, or pribadi
                $scheduleData['start_date'] = $bookingDate->toDateString();
                $scheduleData['end_date'] = $bookingDate->toDateString();
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
        \Log::info('Reject booking called', [
            'booking_id' => $id,
            'reason' => $request->rejection_reason
        ]);

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $booking = Booking::findOrFail($id);
        
        \Log::info('Booking found', [
            'booking_id' => $booking->id,
            'current_status' => $booking->status
        ]);
        
        DB::transaction(function () use ($booking, $request) {
            // Delete related schedule if exists
            if ($booking->schedule) {
                $booking->schedule->delete();
            }
            
            // Update booking status
            $booking->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'handled_by' => auth()->id(),
                'handled_at' => now()
            ]);
            
            \Log::info('Booking rejected successfully', [
                'booking_id' => $booking->id,
                'new_status' => $booking->status
            ]);
        });

        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil ditolak.');
    }
}
