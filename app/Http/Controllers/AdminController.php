<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\BookingApproved;
use App\Mail\BookingRejected;

class AdminController extends Controller
{
    /**
     * Show admin dashboard with pending bookings
     */
    public function dashboard()
    {
        $pendingBookings = Booking::with('lab')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedBookings = Booking::with('lab')
            ->where('status', 'approved')
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get();

        $rejectedBookings = Booking::with('lab')
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->take(10)
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
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Create schedule entry from approved booking
            // Important: Use Carbon with Asia/Jakarta timezone to get correct day
            $bookingDate = \Carbon\Carbon::parse($booking->tanggal)->timezone('Asia/Jakarta');
            
            $scheduleData = [
                'lab_id' => $booking->lab_id,
                'day' => $booking->day, // Use day from booking (already correct)
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'booking_id' => $booking->id,
            ];

            // Tentukan type dan fields berdasarkan booking_type
            if ($booking->is_recurring) {
                // Perkuliahan tetap - recurring schedule
                $scheduleData['type'] = 'booking_recurring';
                $scheduleData['start_date'] = $bookingDate->toDateString(); // Format: Y-m-d
                $scheduleData['end_date'] = null; // Recurring tanpa batas
                $scheduleData['course'] = $booking->mata_kuliah;
                $scheduleData['lecturer'] = $booking->dosen_pengampu;
            } else {
                // One-time booking (perkuliahan tidak tetap atau non-perkuliahan)
                $scheduleData['type'] = 'booking_onetime';
                $scheduleData['start_date'] = $bookingDate->toDateString(); // Format: Y-m-d
                $scheduleData['end_date'] = $bookingDate->toDateString();
                
                if ($booking->booking_type === 'non_perkuliahan') {
                    $scheduleData['course'] = $booking->nama_kegiatan;
                    $scheduleData['lecturer'] = $booking->nama_peminjam;
                } else {
                    $scheduleData['course'] = $booking->mata_kuliah;
                    $scheduleData['lecturer'] = $booking->dosen_pengampu;
                }
            }

            Schedule::create($scheduleData);
        });

        // Send approval email (DISABLED - Uncomment saat email sudah ready)
        // try {
        //     Mail::to($booking->email)->send(new BookingApproved($booking));
        // } catch (\Exception $e) {
        //     \Log::warning('Failed to send booking approval email', [
        //         'booking_id' => $booking->id,
        //         'error' => $e->getMessage()
        //     ]);
        // }

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
                'rejection_reason' => $request->rejection_reason
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
