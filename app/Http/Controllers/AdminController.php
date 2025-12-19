<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

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
        $booking->update(['status' => 'approved']);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil disetujui!');
    }

    /**
     * Reject booking
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman ditolak.');
    }
}
