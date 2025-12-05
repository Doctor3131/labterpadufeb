<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;

class LandingController extends Controller
{
    /**
     * Show landing page with schedule information
     */
    public function index()
    {
        // Get schedules grouped by day
        $schedules = Schedule::orderBy('start_time')
            ->get()
            ->groupBy('day');

        return view('landing', compact('schedules'));
    }
}
