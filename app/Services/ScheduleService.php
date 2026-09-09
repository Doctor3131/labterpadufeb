<?php

namespace App\Services;

use App\Helpers\DayHelper;
use App\Models\Booking;
use App\Models\Lab;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    public function __construct(private readonly ScheduleCalendarService $calendar) {}

    protected $months = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
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
     *
     * @param  array  $labIds  Filter by lab IDs (empty array = all labs)
     */
    public function getWeekSchedules(Carbon $startOfWeek, Carbon $endOfWeek, array $labIds = []): Collection
    {
        $activeLabs = Lab::where('status', 'available')
            ->when($labIds !== [], fn ($query) => $query->whereIn('id', $labIds))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($activeLabs === []) {
            return collect();
        }

        return $this->calendar
            ->events($startOfWeek, $endOfWeek, $activeLabs, false)
            ->map(function (array $event) {
                $event['id'] = 'sched_'.$event['schedule_id'];
                $event['date_formatted'] = $this->formatDateForDisplay(Carbon::parse($event['date']));

                return $event;
            });
    }

    /**
     * Format a date for Indonesian display
     */
    public function formatDateForDisplay(Carbon $date): string
    {
        $dayName = DayHelper::fromDate($date);
        $month = $this->months[$date->format('m')];

        return "$dayName, ".$date->format('j')." $month ".$date->format('Y');
    }

    /**
     * Format a time range for display
     */
    public function formatTimeRange($startTime, $endTime): string
    {
        // Handle both string and Carbon
        $start = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
        $end = $endTime instanceof Carbon ? $endTime : Carbon::parse($endTime);

        return $start->format('H:i').' - '.$end->format('H:i');
    }

    /**
     * Get Indonesian formatted label for week range
     */
    public function getWeekLabel(Carbon $start, Carbon $end): string
    {
        $startLabel = $start->format('j').' '.$this->months[$start->format('m')];
        $endLabel = $end->format('j').' '.$this->months[$end->format('m')].' '.$end->format('Y');

        return $startLabel.' - '.$endLabel;
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
            'recurrence_days' => $booking->recurrence_days,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'booking_id' => $booking->id,
            'student_count' => $booking->participant_count,
        ];

        // Determine type and dates
        if ($booking->is_recurring) {
            // Keep the original booking type. A recurring non-fixed lecture is
            // still non-fixed; its recurrence is represented by recurrence_days.
            $scheduleData['type'] = $booking->booking_type;
            $scheduleData['start_date'] = $bookingDate->toDateString();
            $scheduleData['end_date'] = $booking->end_date ? $booking->end_date->toDateString() : null;

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
        if ($startDate) {
            $date = Carbon::parse($startDate);
            $lastDate = Carbon::parse($endDate ?? $startDate);
            $checked = 0;

            while ($date->lte($lastDate) && $checked < 60) {
                $conflict = app(ScheduleCalendarService::class)->findConflict(
                    (int) $labId,
                    $date,
                    $startTime,
                    $endTime,
                    $excludeScheduleId
                );

                if ($conflict) {
                    return $conflict['label'].' pada '.$date->format('d/m/Y');
                }

                $date->addWeek();
                $checked++;
            }

            return null;
        }

        // Legacy date-less schedules cannot be expanded into concrete occurrences.
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

        $conflicting = $query->first();

        if ($conflicting) {
            $timeRange = Carbon::parse($conflicting->start_time)->format('H:i').
                         ' - '.
                         Carbon::parse($conflicting->end_time)->format('H:i');

            return $conflicting->course.' ('.$timeRange.')';
        }

        return null;
    }
}
