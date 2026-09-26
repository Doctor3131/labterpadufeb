<?php

namespace App\Services;

use App\Helpers\DayHelper;
use App\Models\Lab;
use App\Models\Schedule;
use App\Models\ScheduleChangeLog;
use App\Models\ScheduleOccurrence;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleChangeService
{
    private const EDITABLE_FIELDS = [
        'lab_id', 'day', 'start_date', 'end_date', 'start_time', 'end_time',
        'recurrence_days',
        'course', 'lecturer', 'type', 'activity_type', 'komting', 'komting_phone',
        'student_count', 'position', 'equipment_needs',
    ];

    public function __construct(
        private readonly ScheduleCalendarService $calendar,
        private readonly RecurrenceDateService $recurrenceDates
    ) {}

    public function moveOccurrence(
        Schedule $schedule,
        Carbon $originalDate,
        Carbon $targetDate,
        int $labId,
        string $startTime,
        string $endTime,
        string $reason,
        ?int $userId
    ): ScheduleOccurrence {
        $this->assertEditableDate($originalDate);
        $this->assertValidOccurrence($schedule, $originalDate);
        $this->assertOperatingDate($targetDate);

        return DB::transaction(function () use ($schedule, $originalDate, $targetDate, $labId, $startTime, $endTime, $reason, $userId) {
            Lab::query()->lockForUpdate()->findOrFail($labId);

            $conflict = $this->calendar->findConflict(
                $labId,
                $targetDate,
                $startTime,
                $endTime,
                $schedule->id,
                $originalDate->toDateString()
            );

            if ($conflict) {
                throw ValidationException::withMessages([
                    'conflict' => 'Slot bentrok dengan '.$conflict['label'].'.',
                ]);
            }

            $before = $schedule->occurrences()
                ->whereDate('occurrence_date', $originalDate)
                ->first()?->toArray();

            $occurrence = $this->upsertOccurrence($schedule, $originalDate, [
                'type' => ScheduleOccurrence::TYPE_MOVED,
                'override_date' => $targetDate->toDateString(),
                'lab_id' => $labId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'change_reason' => $reason,
                'changed_by' => $userId,
            ]);

            $this->log($schedule, $occurrence, 'move', 'single', $originalDate, $before, $occurrence->fresh()->toArray(), $reason, $userId);

            return $occurrence;
        });
    }

    public function cancelOccurrence(
        Schedule $schedule,
        Carbon $originalDate,
        string $reason,
        ?int $userId
    ): ScheduleOccurrence {
        $this->assertEditableDate($originalDate);
        $this->assertValidOccurrence($schedule, $originalDate);

        return DB::transaction(function () use ($schedule, $originalDate, $reason, $userId) {
            $before = $schedule->occurrences()
                ->whereDate('occurrence_date', $originalDate)
                ->first()?->toArray();

            $occurrence = $this->upsertOccurrence($schedule, $originalDate, [
                'type' => ScheduleOccurrence::TYPE_CANCELLED,
                'override_date' => null,
                'lab_id' => null,
                'start_time' => null,
                'end_time' => null,
                'change_reason' => $reason,
                'changed_by' => $userId,
            ]);

            $this->log($schedule, $occurrence, 'cancel', 'single', $originalDate, $before, $occurrence->fresh()->toArray(), $reason, $userId);

            return $occurrence;
        });
    }

    /**
     * Split a recurring series from an original occurrence onward.
     * The old segment is retained, so completed occurrences remain historical.
     */
    public function changeFuture(
        Schedule $schedule,
        Carbon $originalDate,
        Carbon $newStartDate,
        array $changes,
        string $reason,
        ?int $userId
    ): Schedule {
        if (! $this->calendar->isRecurringSchedule($schedule)) {
            throw ValidationException::withMessages(['scope' => 'Perubahan rangkaian hanya tersedia untuk jadwal berulang.']);
        }

        $this->assertEditableDate($originalDate);
        $this->assertValidOccurrence($schedule, $originalDate);
        $this->assertOperatingDate($newStartDate);

        $changes = Arr::only($changes, self::EDITABLE_FIELDS);
        $changes['day'] = DayHelper::fromDate($newStartDate);
        $changes['start_date'] = $newStartDate->toDateString();
        $changes['end_date'] ??= $schedule->end_date?->toDateString();

        if (! $changes['end_date']) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal selesai wajib untuk jadwal berulang.']);
        }

        $endDate = Carbon::parse($changes['end_date']);
        if ($endDate->lt($newStartDate)) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal selesai harus setelah tanggal mulai baru.']);
        }

        return DB::transaction(function () use ($schedule, $originalDate, $newStartDate, $changes, $reason, $userId) {
            Lab::query()->lockForUpdate()->findOrFail((int) ($changes['lab_id'] ?? $schedule->lab_id));
            $this->assertSeriesHasNoConflict($schedule, $originalDate, $newStartDate, Carbon::parse($changes['end_date']), $changes);

            /** @var Schedule $locked */
            $locked = Schedule::with(['document', 'booking'])->lockForUpdate()->findOrFail($schedule->id);
            $before = Arr::only($locked->toArray(), self::EDITABLE_FIELDS);

            // No occurrence predates the split: updating in place cannot rewrite history.
            if ($locked->start_date && $originalDate->isSameDay($locked->start_date)) {
                $locked->update($changes);
                $this->log($locked, null, 'update', 'future', $originalDate, $before, Arr::only($locked->fresh()->toArray(), self::EDITABLE_FIELDS), $reason, $userId);

                return $locked->fresh();
            }

            $locked->update(['end_date' => $originalDate->copy()->subWeek()->toDateString()]);

            $newSchedule = $locked->replicate();
            $newSchedule->fill($changes);
            $newSchedule->series_uuid = $locked->series_uuid;
            $newSchedule->parent_schedule_id = $locked->id;
            $newSchedule->revision_number = ((int) $locked->revision_number) + 1;
            $newSchedule->save();

            $dateShift = $originalDate->diffInDays($newStartDate, false);
            $locked->occurrences()
                ->whereDate('occurrence_date', '>=', $originalDate)
                ->get()
                ->each(function (ScheduleOccurrence $occurrence) use ($newSchedule, $dateShift) {
                    $occurrence->schedule_id = $newSchedule->id;
                    $occurrence->occurrence_date = $occurrence->occurrence_date->copy()->addDays($dateShift);
                    if ($occurrence->override_date) {
                        $occurrence->override_date = $occurrence->override_date->copy()->addDays($dateShift);
                    }
                    $occurrence->save();
                });

            if ($locked->document) {
                $document = $locked->document->replicate();
                $document->schedule_id = $newSchedule->id;
                $document->save();
            }

            $this->log($locked, null, 'truncate', 'future', $originalDate, $before, ['end_date' => $locked->end_date?->toDateString()], $reason, $userId);
            $this->log($newSchedule, null, 'create_revision', 'future', $newStartDate, null, Arr::only($newSchedule->toArray(), self::EDITABLE_FIELDS), $reason, $userId);

            return $newSchedule->fresh();
        });
    }

    public function changeEndDate(
        Schedule $schedule,
        Carbon $endDate,
        string $reason,
        ?int $userId
    ): Schedule {
        $today = now('Asia/Jakarta')->startOfDay();
        if ($endDate->lt($today)) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal selesai tidak boleh menghapus jadwal yang sudah terlaksana.']);
        }

        if ($schedule->start_date && $endDate->lt($schedule->start_date)) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal selesai harus setelah tanggal mulai.']);
        }

        return DB::transaction(function () use ($schedule, $endDate, $reason, $userId) {
            Lab::query()->lockForUpdate()->findOrFail($schedule->lab_id);

            if ($schedule->end_date && $endDate->gt($schedule->end_date)) {
                $dates = $this->recurrenceDates->datesForScheduleTypeRange(
                    $schedule->type,
                    $schedule->end_date->copy()->addDay(),
                    $endDate,
                    $schedule->recurrence_days,
                    $schedule->day,
                    $schedule->start_date,
                    $endDate
                );

                foreach ($dates as $dateString) {
                    $date = Carbon::parse($dateString);
                    $conflict = $this->calendar->findConflict(
                        $schedule->lab_id,
                        $date,
                        $schedule->start_time,
                        $schedule->end_time,
                        $schedule->id
                    );

                    if ($conflict) {
                        throw ValidationException::withMessages([
                            'conflict' => 'Perpanjangan bentrok pada '.$date->format('d/m/Y').' dengan '.$conflict['label'].'.',
                        ]);
                    }
                }
            }

            $before = ['end_date' => $schedule->end_date?->toDateString()];
            $schedule->update(['end_date' => $endDate->toDateString()]);
            $this->log($schedule, null, 'change_end_date', 'future', $endDate, $before, ['end_date' => $endDate->toDateString()], $reason, $userId);

            return $schedule->fresh();
        });
    }

    public function cancelFuture(
        Schedule $schedule,
        Carbon $originalDate,
        string $reason,
        ?int $userId
    ): Schedule {
        if (! $this->calendar->isRecurringSchedule($schedule)) {
            throw ValidationException::withMessages(['scope' => 'Pembatalan rangkaian hanya tersedia untuk jadwal berulang.']);
        }

        $this->assertEditableDate($originalDate);
        $this->assertValidOccurrence($schedule, $originalDate);

        return DB::transaction(function () use ($schedule, $originalDate, $reason, $userId) {
            $before = ['end_date' => $schedule->end_date?->toDateString()];
            // Keep any earlier weekday occurrences in the same week intact.
            $newEndDate = $originalDate->copy()->subDay();
            $schedule->update(['end_date' => $newEndDate->toDateString()]);

            $this->log(
                $schedule,
                null,
                'cancel',
                'future',
                $originalDate,
                $before,
                ['end_date' => $newEndDate->toDateString()],
                $reason,
                $userId
            );

            return $schedule->fresh();
        });
    }

    private function assertSeriesHasNoConflict(
        Schedule $schedule,
        Carbon $originalDate,
        Carbon $newStartDate,
        Carbon $endDate,
        array $changes
    ): void {
        $dates = $this->recurrenceDates->datesForScheduleTypeRange(
            $changes['type'] ?? $schedule->type,
            $newStartDate,
            $endDate,
            $changes['recurrence_days'] ?? $schedule->recurrence_days,
            $changes['day'] ?? $schedule->day,
            $newStartDate,
            $endDate
        );

        foreach ($dates as $dateString) {
            $date = Carbon::parse($dateString);
            $conflict = $this->calendar->findConflict(
                (int) ($changes['lab_id'] ?? $schedule->lab_id),
                $date,
                $changes['start_time'] ?? $schedule->start_time,
                $changes['end_time'] ?? $schedule->end_time,
                $schedule->id,
                null
            );

            if ($conflict) {
                throw ValidationException::withMessages([
                    'conflict' => 'Jadwal bentrok pada '.$date->format('d/m/Y').' dengan '.$conflict['label'].'.',
                ]);
            }

        }
    }

    private function assertEditableDate(Carbon $date): void
    {
        if ($date->lt(now('Asia/Jakarta')->startOfDay())) {
            throw ValidationException::withMessages(['occurrence_date' => 'Jadwal yang sudah lewat tidak dapat diubah.']);
        }
    }

    private function assertValidOccurrence(Schedule $schedule, Carbon $date): void
    {
        if (! $this->calendar->isOccurrenceDate($schedule, $date)) {
            throw ValidationException::withMessages(['occurrence_date' => 'Tanggal tersebut bukan bagian dari rangkaian jadwal ini.']);
        }
    }

    private function assertOperatingDate(Carbon $date): void
    {
        if ($date->isSunday()) {
            throw ValidationException::withMessages(['target_date' => 'Laboratorium tidak dijadwalkan pada hari Minggu.']);
        }
    }

    /**
     * Find an occurrence by calendar date before creating it.
     *
     * SQLite stores date casts with a time component in some local databases,
     * so an exact `occurrence_date = YYYY-MM-DD` lookup can miss an existing
     * row and incorrectly attempt a duplicate insert.
     */
    private function upsertOccurrence(Schedule $schedule, Carbon $date, array $values): ScheduleOccurrence
    {
        try {
            $occurrence = $schedule->occurrences()
                ->whereDate('occurrence_date', $date->toDateString())
                ->first();

            if ($occurrence) {
                $occurrence->fill($values);
                $occurrence->save();

                return $occurrence;
            }

            return $schedule->occurrences()->create(array_merge([
                'occurrence_date' => $date->toDateString(),
            ], $values));
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000'
                && str_contains($exception->getMessage(), 'schedule_occurrences')) {
                throw ValidationException::withMessages([
                    'occurrence_date' => 'Pertemuan ini baru saja berubah. Muat ulang kalender, lalu coba lagi.',
                ]);
            }

            throw $exception;
        }
    }

    private function log(
        Schedule $schedule,
        ?ScheduleOccurrence $occurrence,
        string $action,
        string $scope,
        Carbon $effectiveDate,
        ?array $before,
        ?array $after,
        string $reason,
        ?int $userId
    ): void {
        ScheduleChangeLog::create([
            'schedule_id' => $schedule->id,
            'schedule_occurrence_id' => $occurrence?->id,
            'series_uuid' => $schedule->series_uuid,
            'action' => $action,
            'scope' => $scope,
            'effective_date' => $effectiveDate->toDateString(),
            'before_values' => $before,
            'after_values' => $after,
            'reason' => $reason,
            'changed_by' => $userId,
        ]);
    }
}
