<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Get schedules for a specific week with concrete dates
     */
    public function getWeekSchedules(Request $request)
    {
        try {
            // Get week offset (0 = current week, 1 = next week, -1 = previous week)
            $weekOffset = (int) $request->input('week_offset', 0);
            
            // Calculate start of week (Monday)
            $startOfWeek = Carbon::now('Asia/Jakarta')
                ->startOfWeek(Carbon::MONDAY)
                ->addWeeks($weekOffset);
        
        // Get all labs
        $labs = Lab::with(['schedules' => function ($query) use ($startOfWeek) {
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);
            $query->where(function ($q) use ($startOfWeek, $endOfWeek) {
                $q->where(function ($q2) use ($startOfWeek, $endOfWeek) {
                    // Schedule is active during this week
                    $q2->whereNull('start_date')
                       ->orWhere(function ($q3) use ($startOfWeek, $endOfWeek) {
                           $q3->where('start_date', '<=', $endOfWeek->format('Y-m-d'))
                              ->where(function ($q4) use ($startOfWeek) {
                                  $q4->whereNull('end_date')
                                     ->orWhere('end_date', '>=', $startOfWeek->format('Y-m-d'));
                              });
                       });
                });
            });
        }])->get();
        
        // Transform schedules with concrete dates
        $weekSchedules = [];
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        
        foreach ($labs as $lab) {
            foreach ($lab->schedules as $schedule) {
                // Find the concrete date for this schedule in the current week
                $dayIndex = array_search($schedule->day, $dayNames);
                if ($dayIndex !== false) {
                    $concreteDate = $startOfWeek->copy()->addDays($dayIndex);
                    
                    // Manual format for Indonesian
                    $months = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    $days = [
                        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                    ];
                    
                    $dayName = $days[$concreteDate->format('l')];
                    $day = $concreteDate->format('j');
                    $month = $months[$concreteDate->format('m')];
                    $year = $concreteDate->format('Y');
                    
                    // Format time safely
                    $startTime = $schedule->start_time;
                    $endTime = $schedule->end_time;
                    
                    // If time is already in HH:MM:SS format, just take first 5 chars
                    if (is_string($startTime) && strlen($startTime) >= 5) {
                        $startTime = substr($startTime, 0, 5);
                    }
                    if (is_string($endTime) && strlen($endTime) >= 5) {
                        $endTime = substr($endTime, 0, 5);
                    }
                    
                    $weekSchedules[] = [
                        'id' => $schedule->id,
                        'lab' => $lab->name,
                        'lab_id' => $lab->id,
                        'day' => $schedule->day,
                        'date' => $concreteDate->format('Y-m-d'),
                        'date_formatted' => "$dayName, $day $month $year",
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'course' => $schedule->course,
                        'lecturer' => $schedule->lecturer,
                        'komting' => $schedule->komting,
                        'student_count' => $schedule->student_count,
                    ];
                }
            }
        }
        
        // Sort by date and time
        usort($weekSchedules, function ($a, $b) {
            $dateCompare = strcmp($a['date'], $b['date']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return strcmp($a['start_time'], $b['start_time']);
        });
        
        // Format week label in Indonesian
        $weekStart = $startOfWeek->copy()->locale('id');
        $weekEnd = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY)->locale('id');
        
        // Manually format months in Indonesian
        $monthsLabel = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        $startLabel = $weekStart->format('j') . ' ' . $monthsLabel[$weekStart->format('m')];
        $endLabel = $weekEnd->format('j') . ' ' . $monthsLabel[$weekEnd->format('m')] . ' ' . $weekEnd->format('Y');
        
        return response()->json([
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'week_label' => $startLabel . ' - ' . $endLabel,
            'schedules' => $weekSchedules,
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
