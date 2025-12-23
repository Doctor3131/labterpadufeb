<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Booking;
use Carbon\Carbon;

class LandingController extends Controller
{
    /**
     * Show landing page with schedule information
     */
    public function index()
    {
        // Get current week (Monday to Sunday)
        $startOfWeek = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        // Get schedules for current week with concrete dates
        $schedules = $this->getWeekSchedulesWithDates($startOfWeek);

        return view('landing', compact('schedules', 'startOfWeek'));
    }

    /**
     * Get schedules with concrete dates for the week
     */
    private function getWeekSchedulesWithDates($startOfWeek)
    {
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);
        
        // Get recurring schedules (from schedules table) - eager load booking
        $recurringSchedules = Schedule::with(['lab', 'booking'])
            ->where(function ($query) use ($startOfWeek, $endOfWeek) {
                $query->where(function ($q) use ($startOfWeek, $endOfWeek) {
                    $q->whereNull('start_date')
                      ->orWhere(function ($q2) use ($startOfWeek, $endOfWeek) {
                          $q2->where('start_date', '<=', $endOfWeek->format('Y-m-d'))
                             ->where(function ($q3) use ($startOfWeek) {
                                 $q3->whereNull('end_date')
                                    ->orWhere('end_date', '>=', $startOfWeek->format('Y-m-d'));
                             });
                      });
                });
            })
            ->orderBy('start_time')
            ->get();

        // Group schedules by day with concrete dates
        $groupedSchedules = [];
        
        foreach ($dayNames as $index => $day) {
            $concreteDate = $startOfWeek->copy()->addDays($index);
            $groupedSchedules[$day] = [
                'date' => $concreteDate->format('Y-m-d'),
                'date_formatted' => $concreteDate->isoFormat('dddd, D MMMM Y'),
                'items' => []
            ];

            // Add recurring schedules for this day
            $daySchedules = $recurringSchedules->where('day', $day);
            foreach ($daySchedules as $schedule) {
                // If it's a one-time booking, ensure the date matches exactly
                // If it's a recurring schedule, the query already filtered for the valid date range
                if ($schedule->type === 'booking_onetime' && $schedule->start_date && $schedule->start_date->format('Y-m-d') !== $concreteDate->format('Y-m-d')) {
                    continue;
                }

                $groupedSchedules[$day]['items'][] = [
                    'type' => 'recurring',
                    'lab' => $schedule->lab,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    // Use smart accessors from Schedule model which fallback to booking logic
                    'course' => $schedule->course, 
                    'lecturer' => $schedule->lecturer,
                    'komting' => $schedule->komting,
                    'student_count' => $schedule->student_count,
                    'booking_type' => $schedule->booking ? $schedule->booking->booking_type : null,
                ];
            }

            // Sort items by start time
            usort($groupedSchedules[$day]['items'], function ($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
        }

        return $groupedSchedules;
    }
}
