<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class PersonalBorrowingController extends Controller
{
    /**
     * Show all personal borrowings (report/log view)
     * Queries from bookings table where booking_type = 'pribadi'
     */
    public function index()
    {
        $borrowings = Booking::where('booking_type', 'pribadi')
            ->with('handler')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.personal-borrowings', compact('borrowings'));
    }
}
