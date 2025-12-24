<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\Booking;

use App\Services\ScheduleService;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Get schedules for a specific week with concrete dates
     */
    public function getWeekSchedules(Request $request)
    {
        try {
            // Get week offset
            $weekOffset = (int) $request->input('week_offset', 0);
            
            // Get week range from service
            [$startOfWeek, $endOfWeek] = $this->scheduleService->getWeekRange($weekOffset);
            
            // Get all schedules (regular + bookings)
            $weekSchedules = $this->scheduleService->getWeekSchedules($startOfWeek, $endOfWeek);
            
            // Format week label
            $weekLabel = $this->scheduleService->getWeekLabel($startOfWeek, $endOfWeek);
            
            return response()->json([
                'week_start' => $startOfWeek->format('Y-m-d'),
                'week_end' => $endOfWeek->format('Y-m-d'),
                'week_label' => $weekLabel,
                'schedules' => $weekSchedules->values(), // Reset keys for JSON array
            ]);
        
        } catch (\Exception $e) {
            \Log::error('Error in getWeekSchedules: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}
