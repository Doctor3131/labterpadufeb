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
            // Get lab filter (comma-separated lab IDs)
            $labIds = $request->input('labs') ? explode(',', $request->input('labs')) : [];
            
            // If a specific date is provided, calculate week from that date
            // Otherwise use week_offset
            if ($request->has('date')) {
                $targetDate = Carbon::parse($request->input('date'))->startOfDay();
                $startOfWeek = $targetDate->copy()->startOfWeek(Carbon::MONDAY);
                $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);
            } else {
                $weekOffset = (int) $request->input('week_offset', 0);
                [$startOfWeek, $endOfWeek] = $this->scheduleService->getWeekRange($weekOffset);
            }
            
            // Get all schedules (regular + bookings)
            $weekSchedules = $this->scheduleService->getWeekSchedules($startOfWeek, $endOfWeek, $labIds);
            
            // Format week label
            $weekLabel = $this->scheduleService->getWeekLabel($startOfWeek, $endOfWeek);
            
            // Get all active labs for filter dropdown
            $labs = Lab::where('status', 'available')->orderBy('name')->get(['id', 'name']);
            
            return response()->json([
                'week_start' => $startOfWeek->format('Y-m-d'),
                'week_end' => $endOfWeek->format('Y-m-d'),
                'week_label' => $weekLabel,
                'schedules' => $weekSchedules->values(), // Reset keys for JSON array
                'labs' => $labs,
            ]);
        
        } catch (\Exception $e) {
            \Log::error('Error in getWeekSchedules: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat jadwal. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Display fullscreen TV mode for schedule display
     */
    public function display(Request $request)
    {
        // Get week range (current week)
        [$startOfWeek, $endOfWeek] = $this->scheduleService->getWeekRange(0);
        
        // Get all schedules for the week
        $weekSchedules = $this->scheduleService->getWeekSchedules($startOfWeek, $endOfWeek);
        
        // Organize by day
        $schedules = [];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        foreach ($days as $day) {
            $daySchedules = $weekSchedules->filter(fn($s) => $s['day'] === $day)->values();
            $schedules[$day] = [
                'items' => $daySchedules,
                'date' => $startOfWeek->copy()->next(strtolower($day === 'Senin' ? 'Monday' : ($day === 'Selasa' ? 'Tuesday' : ($day === 'Rabu' ? 'Wednesday' : ($day === 'Kamis' ? 'Thursday' : ($day === 'Jumat' ? 'Friday' : 'Saturday'))))))->format('Y-m-d'),
            ];
        }
        
        // Fix Senin date (it's the start of week)
        $schedules['Senin']['date'] = $startOfWeek->format('Y-m-d');
        
        // Get all active labs for grid columns
        $labs = \App\Models\Lab::where('status', 'available')->orderBy('name')->get(['id', 'name']);
        
        return view('schedules.display', [
            'schedules' => $schedules,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'labs' => $labs,
        ]);
    }
}
