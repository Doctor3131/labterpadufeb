<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LabController extends Controller
{
    /**
     * Check lab availability for given date and time
     * Returns list of all labs with availability status
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $date = $request->date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        // Get all labs
        $labs = Lab::all();

        // Check each lab for conflicts
        $labsWithAvailability = $labs->map(function ($lab) use ($date, $startTime, $endTime) {
            // Check if there's any booking (pending or approved) that conflicts
            $hasConflict = Booking::where('lab_id', $lab->id)
                ->where('booking_date', $date)
                ->where('status', '!=', 'rejected') // Exclude rejected bookings
                ->where(function ($query) use ($startTime, $endTime) {
                    // Time overlap logic:
                    // Conflict if: (start_time < end_time) AND (end_time > start_time)
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->whereTime('start_time', '<', $endTime)
                          ->whereTime('end_time', '>', $startTime);
                    });
                })                ->exists();

            return [
                'id' => $lab->id,
                'name' => $lab->name,
                'available' => !$hasConflict,
                'reason' => $hasConflict ? 'Sudah ada booking yang diajukan di jam dan di lab ini' : null,
            ];
        });

        return response()->json($labsWithAvailability);
    }
}
