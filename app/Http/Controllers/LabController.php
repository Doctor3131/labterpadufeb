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
     * Uses Lab::isAvailable() for consistency with form submission validation
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

        // Get day name from date
        $dayMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];
        $dayName = $dayMap[Carbon::parse($date)->dayOfWeek];

        // Get all labs
        $labs = Lab::all();

        // Check each lab using the centralized isAvailable() method
        // This ensures consistency with BookingController validation
        $labsWithAvailability = $labs->map(function ($lab) use ($dayName, $startTime, $endTime, $date) {
            $isAvailable = $lab->isAvailable($dayName, $startTime, $endTime, $date);

            return [
                'id' => $lab->id,
                'name' => $lab->name,
                'available' => $isAvailable,
                'reason' => !$isAvailable ? 'Sudah ada booking yang diajukan di jam dan di lab ini' : null,
            ];
        });

        return response()->json($labsWithAvailability);
    }
}
