<?php

namespace App\Services;

use App\Helpers\DayHelper;
use App\Models\Booking;
use App\Models\Schedule;
use App\Models\ScheduleOccurrence;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleCalendarService
{
    public function __construct(private readonly RecurrenceDateService $recurrenceDates) {}

    /**
     * Resolve schedule series and exceptions into concrete calendar events.
     */
    public function events(Carbon $start, Carbon $end, array $labIds = [], bool $includePrivate = true): Collection
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->startOfDay();

        $schedules = Schedule::query()
            ->with(['lab', 'booking', 'occurrences.lab'])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($active) use ($start, $end) {
                    $active->where(function ($q) use ($end) {
                        $q->whereNull('start_date')->orWhereDate('start_date', '<=', $end);
                    })->where(function ($q) use ($start) {
                        $q->whereNull('end_date')->orWhereDate('end_date', '>=', $start);
                    });
                })->orWhereHas('occurrences', function ($occurrences) use ($start, $end) {
                    $occurrences->whereBetween('override_date', [$start->toDateString(), $end->toDateString()]);
                });
            })
            ->get();

        return $schedules
            ->flatMap(fn (Schedule $schedule) => $this->eventsForSchedule($schedule, $start, $end, $includePrivate))
            ->filter(function (array $event) use ($labIds) {
                return $labIds === [] || in_array((int) $event['lab_id'], array_map('intval', $labIds), true);
            })
            ->unique(fn (array $event) => $event['schedule_id'].'|'.$event['occurrence_date'])
            ->sortBy(fn (array $event) => $event['date'].' '.$event['start_time'])
            ->values();
    }

    public function eventsForSchedule(
        Schedule $schedule,
        Carbon $start,
        Carbon $end,
        bool $includePrivate = true
    ): Collection {
        if (! $includePrivate && $schedule->booking?->booking_type === 'pribadi') {
            return collect();
        }

        $occurrences = $schedule->occurrences->keyBy(
            fn (ScheduleOccurrence $occurrence) => $occurrence->occurrence_date->toDateString()
        );

        $originalDates = $this->baseOccurrenceDates($schedule, $start, $end);

        // An occurrence may originate outside the requested range and be moved into it.
        $movedIntoRange = $schedule->occurrences
            ->filter(fn (ScheduleOccurrence $occurrence) => $occurrence->override_date
                && $this->isOccurrenceDate($schedule, $occurrence->occurrence_date)
                && $occurrence->override_date->betweenIncluded($start, $end))
            ->map(fn (ScheduleOccurrence $occurrence) => $occurrence->occurrence_date->toDateString());

        return $originalDates
            ->merge($movedIntoRange)
            ->unique()
            ->map(function (string $originalDate) use ($schedule, $occurrences, $start, $end) {
                /** @var ScheduleOccurrence|null $occurrence */
                $occurrence = $occurrences->get($originalDate);

                if ($occurrence?->isCancelled()) {
                    return null;
                }

                $actualDate = $occurrence?->override_date?->toDateString() ?? $originalDate;
                $actual = Carbon::parse($actualDate)->startOfDay();
                if (! $actual->betweenIncluded($start, $end)) {
                    return null;
                }

                $labId = $occurrence?->lab_id ?? $schedule->lab_id;
                $labName = $occurrence?->lab?->name ?? $schedule->lab?->name;
                $startTime = $this->normaliseTime($occurrence?->start_time ?? $schedule->start_time);
                $endTime = $this->normaliseTime($occurrence?->end_time ?? $schedule->end_time);

                return [
                    'id' => 'sched_'.$schedule->id.'_'.$originalDate,
                    'schedule_id' => $schedule->id,
                    'series_uuid' => $schedule->series_uuid,
                    'revision_number' => $schedule->revision_number,
                    'occurrence_id' => $occurrence?->id,
                    'occurrence_date' => $originalDate,
                    'date' => $actualDate,
                    'day' => DayHelper::fromDate($actual),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'lab_id' => $labId,
                    'lab' => $labName,
                    'course' => $schedule->course,
                    'lecturer' => $schedule->lecturer,
                    'komting' => $schedule->komting,
                    'student_count' => $schedule->student_count,
                    'booking_type' => $schedule->booking?->booking_type ?? $schedule->type,
                    'type' => $schedule->type,
                    'is_booking' => (bool) $schedule->booking_id,
                    'is_recurring' => $this->isRecurringSchedule($schedule),
                    'is_exception' => (bool) $occurrence,
                    'is_past' => $actual->lt(now('Asia/Jakarta')->startOfDay()),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Return the first effective conflict for a concrete date/time slot.
     */
    public function findConflict(
        int $labId,
        Carbon $date,
        string $startTime,
        string $endTime,
        ?int $excludeScheduleId = null,
        ?string $excludeOccurrenceDate = null,
        bool $includePendingBookings = true,
        ?int $excludeBookingId = null
    ): ?array {
        $dateString = $date->toDateString();

        $event = $this->events($date, $date, [$labId])
            ->first(function (array $event) use ($startTime, $endTime, $excludeScheduleId, $excludeOccurrenceDate) {
                if ($excludeScheduleId === $event['schedule_id']
                    && ($excludeOccurrenceDate === null || $excludeOccurrenceDate === $event['occurrence_date'])) {
                    return false;
                }

                return $this->timesOverlap($event['start_time'], $event['end_time'], $startTime, $endTime);
            });

        if ($event) {
            return ['source' => 'schedule', 'label' => $event['course'] ?: 'Jadwal lain', 'event' => $event];
        }

        if (! $includePendingBookings) {
            return null;
        }

        $booking = Booking::query()
            ->where('lab_id', $labId)
            ->where('status', 'pending')
            ->when($excludeBookingId, fn ($query) => $query->where('id', '!=', $excludeBookingId))
            ->where(function ($query) use ($dateString) {
                $query->where(function ($single) use ($dateString) {
                    $single->where('is_recurring', false)->whereDate('booking_date', $dateString);
                })->orWhere(function ($recurring) use ($dateString) {
                    $recurring->where('is_recurring', true)
                        ->whereDate('booking_date', '<=', $dateString)
                        ->where(function ($end) use ($dateString) {
                            $end->whereNull('end_date')->orWhereDate('end_date', '>=', $dateString);
                        });
                });
            })
            ->overlappingTime($startTime, $endTime)
            ->get()
            ->first(fn (Booking $candidate) => ! $candidate->is_recurring
                || $this->recurrenceDates->includesBookingDate($candidate, $date));

        return $booking
            ? ['source' => 'booking', 'label' => $booking->course_name ?? $booking->activity_name ?? 'Booking pending']
            : null;
    }

    /**
     * Privacy-safe busy blocks for the public booking calendar.
     */
    public function busyEvents(Carbon $start, Carbon $end, int $labId): Collection
    {
        $scheduleEvents = $this->events($start, $end, [$labId])
            ->map(fn (array $event) => [
                'id' => 'busy_'.$event['id'],
                'title' => 'Tidak tersedia',
                'start' => $event['date'].'T'.$event['start_time'],
                'end' => $event['date'].'T'.$event['end_time'],
                'display' => 'background',
                'backgroundColor' => '#fecaca',
                'borderColor' => '#ef4444',
            ]);

        $pending = Booking::query()
            ->where('lab_id', $labId)
            ->where('status', 'pending')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('booking_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($recurring) use ($start, $end) {
                        $recurring->where('is_recurring', true)
                            ->whereDate('booking_date', '<=', $end)
                            ->where(function ($range) use ($start) {
                                $range->whereNull('end_date')->orWhereDate('end_date', '>=', $start);
                            });
                    });
            })
            ->get()
            ->flatMap(function (Booking $booking) use ($start, $end) {
                return $this->recurrenceDates->datesForBooking($booking)
                    ->map(fn (string $date) => Carbon::parse($date))
                    ->filter(fn (Carbon $date) => $date->betweenIncluded($start, $end))
                    ->map(fn (Carbon $date) => $this->pendingBusyEvent($booking, $date))
                    ->all();
            });

        return $scheduleEvents->concat($pending)->values();
    }

    public function isOccurrenceDate(Schedule $schedule, Carbon $date): bool
    {
        if ($schedule->start_date && $date->lt($schedule->start_date->copy()->startOfDay())) {
            return false;
        }

        if ($schedule->end_date && $date->gt($schedule->end_date->copy()->startOfDay())) {
            return false;
        }

        if (! $this->isRecurringSchedule($schedule)) {
            return $schedule->start_date?->isSameDay($date) ?? false;
        }

        return in_array(
            DayHelper::fromDate($date),
            $this->recurrenceDates->normaliseDays($schedule->recurrence_days, $schedule->day),
            true
        );
    }

    private function baseOccurrenceDates(Schedule $schedule, Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        if (! $this->isRecurringSchedule($schedule)) {
            if (! $schedule->start_date || ! $schedule->start_date->betweenIncluded($rangeStart, $rangeEnd)) {
                return collect();
            }

            return collect([$schedule->start_date->toDateString()]);
        }

        return $this->recurrenceDates->datesForSchedule($schedule, $rangeStart, $rangeEnd);
    }

    public function isRecurringSchedule(Schedule $schedule): bool
    {
        if ($schedule->type === 'perkuliahan_tetap') {
            return true;
        }

        if ($schedule->type !== 'perkuliahan_tidak_tetap') {
            return false;
        }

        return ! empty($schedule->recurrence_days)
            || ($schedule->start_date && $schedule->end_date
                && ! $schedule->start_date->isSameDay($schedule->end_date));
    }

    private function normaliseTime(?string $time): string
    {
        return Carbon::parse($time)->format('H:i');
    }

    private function pendingBusyEvent(Booking $booking, Carbon $date): array
    {
        return [
            'id' => 'busy_booking_'.$booking->id.'_'.$date->toDateString(),
            'title' => 'Menunggu persetujuan',
            'start' => $date->toDateString().'T'.$this->normaliseTime($booking->start_time),
            'end' => $date->toDateString().'T'.$this->normaliseTime($booking->end_time),
            'display' => 'background',
            'backgroundColor' => '#fde68a',
            'borderColor' => '#f59e0b',
        ];
    }

    private function timesOverlap(string $existingStart, string $existingEnd, string $newStart, string $newEnd): bool
    {
        return $existingStart < $newEnd && $existingEnd > $newStart;
    }
}
