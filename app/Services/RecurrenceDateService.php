<?php

namespace App\Services;

use App\Helpers\DayHelper;
use App\Models\Booking;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurrenceDateService
{
    /**
     * Return valid scheduling days in a stable Monday-to-Saturday order.
     */
    public function normaliseDays(?array $days, ?string $fallbackDay = null): array
    {
        $days = array_values(array_unique(array_filter($days ?? [])));

        if ($days === [] && $fallbackDay) {
            $days = [$fallbackDay];
        }

        return collect($days)
            ->filter(fn (string $day) => in_array($day, DayHelper::SCHEDULE_DAYS, true))
            ->sortBy(fn (string $day) => DayHelper::getOrder($day))
            ->values()
            ->all();
    }

    /**
     * Expand a weekly series between two inclusive dates.
     */
    public function datesBetween(Carbon $startDate, Carbon $endDate, ?array $days = null, ?string $fallbackDay = null): Collection
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();
        $days = $this->normaliseDays($days, $fallbackDay ?? DayHelper::fromDate($start));

        if ($start->gt($end) || $days === []) {
            return collect();
        }

        $dates = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (in_array(DayHelper::fromDate($cursor), $days, true)) {
                $dates->push($cursor->toDateString());
            }

            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * Expand a series until it contains the requested number of meetings.
     */
    public function datesForCount(Carbon $startDate, array $days, int $count): Collection
    {
        $days = $this->normaliseDays($days, DayHelper::fromDate($startDate));
        $dates = collect();
        $cursor = $startDate->copy()->startOfDay();
        $guard = 0;

        while ($dates->count() < $count && $guard < 3700) {
            if (in_array(DayHelper::fromDate($cursor), $days, true)) {
                $dates->push($cursor->toDateString());
            }

            $cursor->addDay();
            $guard++;
        }

        return $dates;
    }

    public function endDateForCount(Carbon $startDate, array $days, int $count): ?string
    {
        return $this->datesForCount($startDate, $days, $count)->last();
    }

    public function datesForBooking(Booking $booking): Collection
    {
        if (! $booking->is_recurring || ! $booking->booking_date) {
            return $booking->booking_date ? collect([$booking->booking_date->toDateString()]) : collect();
        }

        $start = $booking->booking_date->copy();
        $end = ($booking->end_date ?? $booking->booking_date)->copy();

        return $this->datesBetween($start, $end, $booking->recurrence_days, $booking->day);
    }

    public function datesForSchedule(Schedule $schedule, Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        $start = $schedule->start_date && $schedule->start_date->gt($rangeStart)
            ? $schedule->start_date
            : $rangeStart;
        $end = $schedule->end_date && $schedule->end_date->lt($rangeEnd)
            ? $schedule->end_date
            : $rangeEnd;

        return $this->datesForScheduleTypeRange(
            $schedule->type,
            $start,
            $end,
            $schedule->recurrence_days,
            $schedule->day,
            $schedule->start_date,
            $schedule->end_date
        );
    }

    /**
     * Expand dates using the semantics of a schedule type.
     *
     * Multi-day non-perkuliahan activities happen on every lab operating day
     * (Monday through Saturday) in their date range. Lecture schedules keep
     * their selected weekly recurrence pattern.
     */
    public function datesForScheduleTypeRange(
        string $type,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        ?array $recurrenceDays = null,
        ?string $fallbackDay = null,
        ?Carbon $seriesStartDate = null,
        ?Carbon $seriesEndDate = null
    ): Collection {
        $isMultiDayActivity = $type === 'non_perkuliahan'
            && $seriesStartDate
            && $seriesEndDate
            && ! $seriesStartDate->isSameDay($seriesEndDate);

        return $this->datesBetween(
            $rangeStart,
            $rangeEnd,
            $isMultiDayActivity ? DayHelper::SCHEDULE_DAYS : $recurrenceDays,
            $fallbackDay
        );
    }

    public function includesBookingDate(Booking $booking, Carbon $date): bool
    {
        return $this->datesForBooking($booking)->contains($date->toDateString());
    }
}
