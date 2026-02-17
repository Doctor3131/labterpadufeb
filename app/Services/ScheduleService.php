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
     * @param Carbon $startOfWeek
     * @param Carbon $endOfWeek
     * @param array $labIds Filter by lab IDs (empty array = all labs)
     */
    public function getWeekSchedules(Carbon $startOfWeek, Carbon $endOfWeek, array $labIds = [], bool $includePribadi = false): Collection
    {
        $schedules = collect();

        // 1. Fetch Regular Schedules
        $labsQuery = Lab::with(['schedules' => function ($query) use ($startOfWeek, $endOfWeek) {
            $query->activeBetweenDates($startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d'));
        }, 'schedules.booking']);
        
        // Apply lab filter if provided
        if (!empty($labIds)) {
            $labsQuery->whereIn('id', $labIds);
        }
        
        $labs = $labsQuery->get();

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

                    // SKIP PRIBADI BOOKINGS - don't show in public schedule (unless admin requests)
                    if (!$includePribadi && $schedule->booking && $schedule->booking->booking_type === 'pribadi') {
                        continue;
                    }

                    // Prepare display data
                    $courseName = $schedule->course;
                    $lecturerName = $schedule->lecturer;
                    $komtingName = $schedule->komting;

                    // Handle overrides for Booking-based schedules
                    if ($schedule->booking) {
                         if ($schedule->booking->booking_type === 'non_perkuliahan') {
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
                        'start_time' => Carbon::parse($schedule->start_time)->format('H:i'),
                        'end_time' => Carbon::parse($schedule->end_time)->format('H:i'),
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
                return strcmp($a['start_time'], $b['start_time']);
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

    /**
     * Map request data to schedule array
     */
    public static function mapFromRequest(array $validated, string $type): array
    {
        // Base schedule data
        $scheduleData = [
            'lab_id' => $validated['lab_id'],
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'type' => $type,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'student_count' => $validated['student_count'],
        ];

        // Map course/lecturer/komting based on type
        if ($type === 'perkuliahan_tetap' || $type === 'perkuliahan_tidak_tetap') {
            $scheduleData['course'] = $validated['course_name'];
            $scheduleData['lecturer'] = $validated['lecturer_name'];
            $scheduleData['komting'] = $validated['komting'] ?? null;
            $scheduleData['komting_phone'] = $validated['komting_phone'] ?? null;
            
            // Clear non-perkuliahan fields
            $scheduleData['activity_type'] = null;
            $scheduleData['position'] = null;
            $scheduleData['equipment_needs'] = null;
        } elseif ($type === 'non_perkuliahan') {
            $scheduleData['course'] = $validated['activity_name'];
            $scheduleData['lecturer'] = $validated['pic_name_non_perkuliahan'] ?? null;
            
            $scheduleData['komting'] = null;
            $scheduleData['komting_phone'] = null; // Non-perkuliahan doesn't have komting; phone stored in schedule_documents

            // Save non-perkuliahan specific fields
            $scheduleData['activity_type'] = $validated['activity_type'] ?? null;
            $scheduleData['position'] = $validated['position'] ?? null;
            $scheduleData['equipment_needs'] = $validated['equipment_needs'] ?? null;
        }

        return $scheduleData;
    }

    /**
     * Map booking model to schedule array
     */
    public static function mapFromBooking(Booking $booking): array
    {
        $bookingDate = Carbon::parse($booking->booking_date);
        
        $scheduleData = [
            'lab_id' => $booking->lab_id,
            'day' => $booking->day,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'booking_id' => $booking->id,
            'student_count' => $booking->participant_count,
        ];

        // Determine type and dates
        if ($booking->is_recurring) {
            $scheduleData['type'] = 'perkuliahan_tetap';
            $scheduleData['start_date'] = $bookingDate->toDateString();
            $scheduleData['end_date'] = null;
            
            $scheduleData['course'] = $booking->course_name;
            $scheduleData['lecturer'] = $booking->lecturer_name;
            $scheduleData['komting'] = $booking->pic_name;
            $scheduleData['komting_phone'] = $booking->phone_number; // Sync phone
        } else {
            // One-time booking
            $scheduleData['type'] = $booking->booking_type;
            $scheduleData['start_date'] = $bookingDate->toDateString();
            $scheduleData['end_date'] = $bookingDate->toDateString();

            if ($booking->booking_type === 'perkuliahan_tidak_tetap') {
                $scheduleData['course'] = $booking->course_name;
                $scheduleData['lecturer'] = $booking->lecturer_name;
                $scheduleData['komting'] = $booking->pic_name;
                $scheduleData['komting_phone'] = $booking->phone_number; // Sync phone
            } elseif ($booking->booking_type === 'non_perkuliahan') {
                $scheduleData['course'] = $booking->activity_name;
                $scheduleData['lecturer'] = $booking->pic_name; 
                $scheduleData['komting'] = null;
                $scheduleData['komting_phone'] = null; // Phone stored in schedule_documents for non_perkuliahan
                
                // Extra fields
                $scheduleData['activity_type'] = $booking->activity_type;
                $scheduleData['position'] = $booking->position;
                $scheduleData['equipment_needs'] = $booking->equipment_needs;
            } else {
                // Fallback
                $scheduleData['course'] = $booking->course_name ?? $booking->activity_name ?? 'Peminjaman';
                $scheduleData['lecturer'] = null;
                $scheduleData['komting'] = null;
                $scheduleData['komting_phone'] = null;
            }
        }

        return $scheduleData;
    }

    /**
     * Check for schedule conflicts
     */
    public static function checkConflict($labId, $day, $startTime, $endTime, $startDate, $endDate, $excludeScheduleId = null)
    {
        $query = Schedule::where('lab_id', $labId)
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                // Time overlap check
                $q->whereTime('start_time', '<', $endTime)
                  ->whereTime('end_time', '>', $startTime);
            });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        // Date overlap check
        if ($startDate || $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                // Overlap logic: (StartA <= EndB) and (EndA >= StartB)
                // Existing Permanent Schedule (StartA=..., EndA=NULL)
                $q->where(function ($q2) use ($startDate, $endDate) {
                    $q2->whereNull('end_date')
                       ->where(function ($q3) use ($endDate, $startDate) {
                           $q3->whereNull('start_date')
                              ->orWhere('start_date', '<=', $endDate ?? $startDate);
                       });
                // Existing Dated Schedule
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->whereNotNull('start_date')
                       ->where('start_date', '<=', $endDate ?? $startDate)
                       ->where(function ($q3) use ($startDate) {
                           $q3->whereNull('end_date')
                              ->orWhere('end_date', '>=', $startDate);
                       });
                });
            });
        }

        $conflicting = $query->first();

        if ($conflicting) {
            $timeRange = Carbon::parse($conflicting->start_time)->format('H:i') . 
                         ' - ' . 
                         Carbon::parse($conflicting->end_time)->format('H:i');
            return $conflicting->course . ' (' . $timeRange . ')';
        }

        return null;
    }
}
