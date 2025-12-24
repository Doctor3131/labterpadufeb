<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Booking;
use Carbon\Carbon;

use App\Services\ScheduleService;

class LandingController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Show landing page with schedule information
     */
    public function index()
    {
        // Get current week range
        [$startOfWeek, $endOfWeek] = $this->scheduleService->getWeekRange(0);

        // Get all schedules
        $allSchedules = $this->scheduleService->getWeekSchedules($startOfWeek, $endOfWeek);

        // Group schedules by day names required by view
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $schedules = [];
        
        // Structure data for view
        foreach ($dayNames as $index => $day) {
            $concreteDate = $startOfWeek->copy()->addDays($index);
            
            // Filter schedules for this day
            $dayItems = $allSchedules->filter(function ($item) use ($day) {
                return $item['day'] === $day;
            });

            $schedules[$day] = [
                'date' => $concreteDate->format('Y-m-d'),
                'date_formatted' => $this->scheduleService->formatDateForDisplay($concreteDate),
                'items' => $dayItems->values()->all() // Convert collection to array
            ];
        }

        return view('landing', compact('schedules', 'startOfWeek'));
    }
}
