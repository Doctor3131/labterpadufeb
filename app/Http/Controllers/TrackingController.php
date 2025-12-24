<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lab;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Show tracking page with token
     */
    public function track($token)
    {
        $booking = Booking::with('lab')
            ->where('tracking_token', $token)
            ->firstOrFail();

        return view('booking.track', compact('booking'));
    }

    /**
     * Cancel booking (only if pending)
     */
    public function cancel($token)
    {
        $booking = Booking::where('tracking_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => 'Dibatalkan oleh peminjam'
        ]);

        return redirect()->route('booking.track', $token)
            ->with('success', 'Peminjaman berhasil dibatalkan.');
    }


    /**
     * Show print view for booking form
     */
    public function print($token)
    {
        $booking = Booking::with('lab')
            ->where('tracking_token', $token)
            ->firstOrFail();

        return view('booking.print', compact('booking'));
    }
}
