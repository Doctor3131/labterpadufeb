<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lab;
use App\Models\Announcement;
use App\Services\ScheduleService;

class LandingController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Show landing page
     */
    public function index()
    {
        // Labs
        $labs = Lab::orderBy('name')->get(['id', 'name']);

        // Active announcements (max 3, newest first)
        $announcements = Announcement::active()->latest()->take(3)->get();

        // Schedule data for current week
        [$startOfWeek, $endOfWeek] = $this->scheduleService->getWeekRange(0);
        $weekSchedules = $this->scheduleService->getWeekSchedules($startOfWeek, $endOfWeek);

        $schedules = [];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        foreach ($days as $day) {
            $daySchedules = $weekSchedules->filter(fn($s) => $s['day'] === $day)->values();
            $schedules[$day] = [
                'items' => $daySchedules,
                'date'  => $startOfWeek->copy()->next(strtolower(match($day) {
                    'Senin'  => 'Monday',
                    'Selasa' => 'Tuesday',
                    'Rabu'   => 'Wednesday',
                    'Kamis'  => 'Thursday',
                    'Jumat'  => 'Friday',
                    default  => 'Saturday',
                }))->format('Y-m-d'),
            ];
        }

        // Fix Senin date (it's the start of week itself)
        $schedules['Senin']['date'] = $startOfWeek->format('Y-m-d');

        return view('landing', compact('labs', 'announcements', 'schedules', 'startOfWeek'));
    }
}
