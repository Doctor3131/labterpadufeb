<?php

namespace App\Services;

use App\Models\Lab;
use App\Models\Schedule;
use App\Models\Booking;
use App\Helpers\DayHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    protected $months = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];

    /**
     * Get start and end dates of the week based on offset
     */
    public function getWeekRange(int $weekOffset = 0): array
    {
        $startOfWeek = Carbon::now('Asia/Jakarta')
            ->startOfWeek(Carbon::MONDAY)
            ->addWeeks($weekOffset);
            
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        return [$startOfWeek, $endOfWeek];
    }

    /**
     * Get all schedules (regular + bookings) for a given week
     */
    public function getWeekSchedules(Carbon $startOfWeek, Carbon $endOfWeek): Collection
    {
        $schedules = collect();

        // 1. Fetch Regular Schedules
        $labs = Lab::with(['schedules' => function ($query) use ($startOfWeek, $endOfWeek) {
            $query->activeBetweenDates($startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d'));
        }, 'schedules.booking'])->get();

        foreach ($labs as $lab) {
            foreach ($lab->schedules as $schedule) {
                 // Find the concrete date for this schedule in the current week
                 $dayIndex = array_search($schedule->day, DayHelper::SCHEDULE_DAYS);
                 if ($dayIndex !== false) {
                    $concreteDate = $startOfWeek->copy()->addDays($dayIndex);
                    
                    // Skip if specific date doesn't match a one-time schedule
                    // One-time types: perkuliahan_tidak_tetap, non_perkuliahan, pribadi
                    $oneTimeTypes = ['perkuliahan_tidak_tetap', 'non_perkuliahan', 'pribadi'];
                    if (in_array($schedule->type, $oneTimeTypes) && $schedule->start_date && $schedule->start_date->format('Y-m-d') !== $concreteDate->format('Y-m-d')) {
                        continue;
                    }

                    // Prepare display data
                    $courseName = $schedule->course;
                    $lecturerName = $schedule->lecturer;
                    $komtingName = $schedule->komting;

                    // Handle overrides for Booking-based schedules
                    if ($schedule->booking) {
                         if ($schedule->booking->booking_type === 'pribadi') {
                             $courseName = 'Peminjaman Pribadi';
                         } elseif ($schedule->booking->booking_type === 'non_perkuliahan') {
                             $courseName = $schedule->booking->activity_name;
                         }
                    }

                    $schedules->push([
                        'id' => 'sched_' . $schedule->id,
                        'lab' => $lab->name,
                        'lab_id' => $lab->id,
                        'day' => $schedule->day,
                        'date' => $concreteDate->format('Y-m-d'),
                        'date_formatted' => $this->formatDateForDisplay($concreteDate),
                        'start_time' => $schedule->start_time instanceof Carbon ? $schedule->start_time : Carbon::parse($schedule->start_time),
                        'end_time' => $schedule->end_time instanceof Carbon ? $schedule->end_time : Carbon::parse($schedule->end_time),
                        'course' => $courseName,
                        'lecturer' => $lecturerName,
                        'komting' => $komtingName,
                        'student_count' => $schedule->student_count,
                        'booking_type' => $schedule->booking ? $schedule->booking->booking_type : $schedule->type,
                        'type' => $schedule->type,
                        'is_booking' => false
                    ]);
                 }
            }
        }

        // Note: Approved bookings should always have schedules (created in AdminController::approve)
        // No need for fallback fetch here - if orphans exist, it's a data integrity issue

        // Sort by Date then Start Time
        return $schedules->sort(function ($a, $b) {
            if ($a['date'] === $b['date']) {
                $timeA = $a['start_time']->format('H:i');
                $timeB = $b['start_time']->format('H:i');
                return strcmp($timeA, $timeB);
            }
            return strcmp($a['date'], $b['date']);
        });
    }

    /**
     * Format a date for Indonesian display
     */
    public function formatDateForDisplay(Carbon $date): string
    {
        $dayName = DayHelper::fromDate($date);
        $month = $this->months[$date->format('m')];
        return "$dayName, " . $date->format('j') . " $month " . $date->format('Y');
    }

    /**
     * Format a time range for display
     */
    public function formatTimeRange($startTime, $endTime): string
    {
        // Handle both string and Carbon
        $start = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
        $end = $endTime instanceof Carbon ? $endTime : Carbon::parse($endTime);
        
        return $start->format('H:i') . ' - ' . $end->format('H:i');
    }
    
    /**
     * Get Indonesian formatted label for week range
     */
    public function getWeekLabel(Carbon $start, Carbon $end): string
    {
        $startLabel = $start->format('j') . ' ' . $this->months[$start->format('m')];
        $endLabel = $end->format('j') . ' ' . $this->months[$end->format('m')] . ' ' . $end->format('Y');
        
        return $startLabel . ' - ' . $endLabel;
    }
}
