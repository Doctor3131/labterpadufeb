<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DayHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lab;
use App\Models\Schedule;
use App\Models\ScheduleChangeLog;
use App\Services\RecurrenceDateService;
use App\Services\ScheduleCalendarService;
use App\Services\ScheduleChangeService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    /**
     * Schedule types for dropdown
     * Note: 'regular' was merged into 'perkuliahan_tetap' as they serve the same purpose
     */
    protected array $types = [
        'perkuliahan_tetap' => 'Perkuliahan Tetap',
        'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
        'non_perkuliahan' => 'Non Perkuliahan',
    ];

    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        // Security Fix: Input Validation
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'month' => 'nullable|date_format:Y-m',
            'lab_id' => 'nullable|exists:labs,id',
            'day' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
            'period' => 'nullable|in:upcoming,history,all',
            'search' => 'nullable|string|max:255',
        ]);

        $query = Schedule::with(['lab', 'booking']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Database-level sorting for performance (instead of in-memory sort)
        // Order: start_date DESC (nulls last) → day order (Senin-Sabtu) → start_time
        $schedules = $query
            ->orderByRaw('CASE WHEN start_date IS NULL THEN 1 ELSE 0 END') // nulls last
            ->orderByDesc('start_date')
            ->orderByRaw("CASE day
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                ELSE 7
            END")
            ->orderBy('start_time')
            ->paginate(100)
            ->withQueryString();

        $labs = Lab::orderBy('name')->get();
        $types = $this->types;

        // Return partial view for AJAX requests
        if ($request->ajax()) {
            return view('admin.schedules.partials.table', compact('schedules'))->render();
        }

        return view('admin.schedules.index', compact('schedules', 'labs', 'types'));
    }

    /**
     * Show the form for creating a new schedule
     */
    public function create()
    {
        $labs = Lab::orderBy('name')->get();
        $days = DayHelper::SCHEDULE_DAYS;
        $types = $this->types;

        return view('admin.schedules.form', [
            'schedule' => null,
            'labs' => $labs,
            'days' => $days,
            'types' => $types,
            'isEdit' => false,
        ]);
    }

    /**
     * Get available labs based on day, time, and date range
     * Used by AJAX to dynamically filter labs that don't have conflicts
     */
    public function getAvailableLabs(Request $request)
    {
        $validated = $request->validate([
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'exclude_schedule_id' => 'nullable|integer|exists:schedules,id',
        ]);

        $day = $validated['day'];
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $scheduleType = $validated['type'] ?? null;
        $recurrenceDays = $validated['recurrence_days'] ?? null;
        $excludeScheduleId = $validated['exclude_schedule_id'] ?? null;

        // Get all labs with schedules and bookings eager loaded
        // Only get labs that are available (not in maintenance)
        $labs = Lab::where('status', 'available')
            ->with(['schedules', 'bookings'])
            ->orderBy('name')
            ->get();

        // Filter available labs
        $availableLabs = $labs->filter(function ($lab) use ($day, $startTime, $endTime, $startDate, $endDate, $recurrenceDays, $excludeScheduleId, $scheduleType) {
            return $this->isLabAvailableForSchedule($lab, $day, $startTime, $endTime, $startDate, $endDate, $recurrenceDays, $excludeScheduleId, $scheduleType);
        });

        // Return labs with id, name, and capacity
        return response()->json($availableLabs->map(function ($lab) {
            return [
                'id' => $lab->id,
                'name' => $lab->name,
                'capacity' => $lab->capacity,
            ];
        })->values());
    }

    /**
     * Check if a lab is available for the given schedule criteria
     * Returns true if no conflicts exist
     */
    private function isLabAvailableForSchedule($lab, $day, $startTime, $endTime, $startDate, $endDate, ?array $recurrenceDays = null, $excludeScheduleId = null, ?string $scheduleType = null)
    {
        $days = app(RecurrenceDateService::class)->normaliseDays($recurrenceDays, $day);

        // Use the same concrete-occurrence resolver as the calendar when
        // dates are available. This includes moved/cancelled occurrences,
        // weekly lecture dates, and operating days in a multi-day activity.
        if ($startDate || $endDate) {
            $rangeStart = Carbon::parse($startDate ?? $endDate);
            $rangeEnd = Carbon::parse($endDate ?? $startDate);
            $recurrenceDates = app(RecurrenceDateService::class);
            $dates = $scheduleType
                ? $recurrenceDates->datesForScheduleTypeRange(
                    $scheduleType,
                    $rangeStart,
                    $rangeEnd,
                    $recurrenceDays,
                    $day,
                    $rangeStart,
                    $rangeEnd
                )
                : $recurrenceDates->datesBetween($rangeStart, $rangeEnd, $days, $day);

            foreach ($dates as $dateString) {
                if (app(ScheduleCalendarService::class)->findConflict(
                    (int) $lab->id,
                    Carbon::parse($dateString),
                    $startTime,
                    $endTime,
                    $excludeScheduleId ? (int) $excludeScheduleId : null
                )) {
                    return false;
                }
            }

            return true;
        }

        // A date-less schedule is a standing reservation. Check every weekday
        // in its recurrence pattern instead of only the primary `day` column.
        $hasScheduleConflict = $lab->schedules->contains(function (Schedule $existing) use ($days, $startTime, $endTime, $excludeScheduleId) {
            if ($excludeScheduleId && (int) $existing->id === (int) $excludeScheduleId) {
                return false;
            }

            $existingDays = app(RecurrenceDateService::class)->normaliseDays(
                $existing->recurrence_days,
                $existing->day
            );

            return array_intersect($days, $existingDays) !== []
                && $this->timesOverlap($existing->start_time, $existing->end_time, $startTime, $endTime);
        });

        if ($hasScheduleConflict) {
            return false;
        }

        return ! $lab->bookings->contains(function (Booking $booking) use ($days, $startTime, $endTime) {
            if ($booking->status !== 'pending') {
                return false;
            }

            $bookingDays = app(RecurrenceDateService::class)->normaliseDays(
                $booking->recurrence_days,
                $booking->day
            );

            return array_intersect($days, $bookingDays) !== []
                && $this->timesOverlap($booking->start_time, $booking->end_time, $startTime, $endTime);
        });
    }

    private function timesOverlap(string $existingStart, string $existingEnd, string $newStart, string $newEnd): bool
    {
        return Carbon::parse($existingStart)->format('H:i') < Carbon::parse($newEnd)->format('H:i')
            && Carbon::parse($existingEnd)->format('H:i') > Carbon::parse($newStart)->format('H:i');
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        // Get validation rules using helper method (DRY)
        $rules = $this->getScheduleValidationRules($request);
        $validated = $request->validate($rules);

        // Map data using Service (DRY)
        $scheduleData = ScheduleService::mapFromRequest($validated, $request->type);
        $startDate = $scheduleData['start_date'] ?? null;
        $endDate = $scheduleData['end_date'] ?? null;
        $recurrenceDays = $scheduleData['recurrence_days'] ?? null;

        // Validate day exists in date range
        $dayValidation = $this->validateDayInDateRange(
            $validated['day'],
            $startDate,
            $endDate,
            $recurrenceDays
        );

        if ($dayValidation !== true) {
            return back()
                ->withErrors(['day' => $dayValidation])
                ->withInput();
        }

        // Check for conflicts
        $conflict = ScheduleService::checkConflict(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $startDate,
            $endDate,
            null,
            $recurrenceDays,
            $request->type
        );

        if ($conflict) {
            return back()
                ->withErrors(['conflict' => 'Jadwal bentrok dengan jadwal yang sudah ada: '.$conflict])
                ->withInput();
        }

        // Check for conflicts with pending bookings - BLOCKER
        $pendingConflict = $this->checkPendingBookings(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $startDate,
            $endDate,
            $recurrenceDays,
            null,
            $request->type
        );

        if ($pendingConflict) {
            return back()
                ->withErrors(['conflict' => $pendingConflict])
                ->withInput();
        }

        $schedule = Schedule::create($scheduleData);

        // Save document fields if any provided
        $this->saveDocumentData($schedule, $request);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a schedule
     */
    public function edit($id)
    {
        $schedule = Schedule::with(['booking', 'document'])->findOrFail($id);
        $changeLogs = ScheduleChangeLog::with('changedBy')
            ->where('series_uuid', $schedule->series_uuid)
            ->latest()
            ->limit(50)
            ->get();
        $labs = Lab::orderBy('name')->get();
        $days = DayHelper::SCHEDULE_DAYS;
        $types = $this->types;

        return view('admin.schedules.form', [
            'schedule' => $schedule,
            'labs' => $labs,
            'days' => $days,
            'types' => $types,
            'isEdit' => true,
            'changeLogs' => $changeLogs,
        ]);
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::with('booking')->findOrFail($id);

        // Get validation rules using helper method (DRY)
        $rules = $this->getScheduleValidationRules($request);
        $validated = $request->validate($rules);

        $scope = $request->input('scope', 'all');
        $isRecurring = app(ScheduleCalendarService::class)->isRecurringSchedule($schedule);
        $scheduleData = ScheduleService::mapFromRequest($validated, $request->type);

        // Scoped updates only apply to recurring day-based series
        if ($scope === 'single' && $isRecurring) {
            return $this->updateSingleOccurrence($schedule, $request, $validated);
        }

        if ($scope === 'future' && $isRecurring) {
            return $this->updateFutureOccurrences($schedule, $request, $validated);
        }

        // "All" means all not-yet-completed occurrences. Past occurrences are immutable.
        if ($isRecurring) {
            $today = now('Asia/Jakarta')->startOfDay();
            $originalDate = $this->nextOccurrenceOnOrAfter($schedule, $today);
            if (! $originalDate) {
                return back()->withErrors(['scope' => 'Rangkaian ini sudah selesai dan menjadi data historis.'])->withInput();
            }

            $targetDate = Carbon::parse($validated['start_date'])->gte($today)
                ? Carbon::parse($validated['start_date'])
                : $this->nextDateForDay($today, $validated['day']);

            $updated = app(ScheduleChangeService::class)->changeFuture(
                $schedule,
                $originalDate,
                $targetDate,
                $scheduleData,
                $validated['change_reason'],
                Auth::id()
            );
            $this->saveDocumentData($updated, $request);

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Semua pertemuan yang belum terlaksana berhasil diperbarui tanpa mengubah histori.');
        }

        if ($schedule->start_date && $schedule->start_date->lt(now('Asia/Jakarta')->startOfDay())) {
            return back()->withErrors(['scope' => 'Jadwal yang sudah terlaksana tidak dapat ditimpa.'])->withInput();
        }

        $startDate = $scheduleData['start_date'] ?? null;
        $endDate = $scheduleData['end_date'] ?? null;
        $recurrenceDays = $scheduleData['recurrence_days'] ?? null;

        // Validate day exists in date range
        $dayValidation = $this->validateDayInDateRange(
            $validated['day'],
            $startDate,
            $endDate,
            $recurrenceDays
        );

        if ($dayValidation !== true) {
            return back()
                ->withErrors(['day' => $dayValidation])
                ->withInput();
        }

        // Check for conflicts (excluding current schedule)
        $conflict = ScheduleService::checkConflict(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $startDate,
            $endDate,
            $id,
            $recurrenceDays,
            $request->type
        );

        if ($conflict) {
            return back()
                ->withErrors(['conflict' => 'Jadwal bentrok dengan jadwal yang sudah ada: '.$conflict])
                ->withInput();
        }

        // Check for conflicts with pending bookings - BLOCKER
        $pendingConflict = $this->checkPendingBookings(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $startDate,
            $endDate,
            $recurrenceDays,
            $schedule->booking_id, // Exclude current booking if schedule is from booking
            $request->type
        );

        if ($pendingConflict) {
            return back()
                ->withErrors(['conflict' => $pendingConflict])
                ->withInput();
        }

        DB::transaction(function () use ($schedule, $scheduleData, $request) {
            // Update schedule
            $schedule->update($scheduleData);

            // Save document fields
            $this->saveDocumentData($schedule, $request);
        });

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui!');
    }

    /**
     * Remove the specified schedule.
     *
     * Accepts an optional scope:
     *  - all    (default): remove the entire series + mark booking deleted.
     *  - single          : cancel a single occurrence on the given date.
     *  - future          : cancel the given occurrence and everything after it.
     */
    public function destroy(Request $request, $id)
    {
        $schedule = Schedule::with('booking')->findOrFail($id);

        $validated = $request->validate([
            'scope' => 'nullable|in:all,single,future',
            'occurrence_date' => 'nullable|date',
            'change_reason' => 'required|string|max:1000',
        ]);

        $scope = $validated['scope'] ?? 'all';
        $occurrenceDate = $validated['occurrence_date'] ?? null;
        $isRecurring = app(ScheduleCalendarService::class)->isRecurringSchedule($schedule);

        $info = $schedule->course.' ('.$schedule->day.')';

        $changes = app(ScheduleChangeService::class);
        if ($isRecurring) {
            $date = $occurrenceDate
                ? Carbon::parse($occurrenceDate)
                : $this->nextOccurrenceOnOrAfter($schedule, now('Asia/Jakarta')->startOfDay());

            if (! $date) {
                return back()->withErrors(['scope' => 'Tidak ada pertemuan mendatang yang dapat dibatalkan.']);
            }

            if ($scope === 'single') {
                $changes->cancelOccurrence($schedule, $date, $validated['change_reason'], Auth::id());
            } else {
                $changes->cancelFuture($schedule, $date, $validated['change_reason'], Auth::id());
            }
        } else {
            $date = $schedule->start_date;
            if (! $date || $date->lt(now('Asia/Jakarta')->startOfDay())) {
                return back()->withErrors(['scope' => 'Jadwal historis tidak dapat dihapus.']);
            }
            $changes->cancelOccurrence($schedule, $date, $validated['change_reason'], Auth::id());
        }

        if ($scope === 'all' && $schedule->booking) {
            $schedule->booking->update([
                'status' => 'deleted',
                'handled_at' => now(),
                'handled_by' => Auth::id(),
            ]);
        }

        $message = match ($scope) {
            'single' => 'Pertemuan tanggal '.Carbon::parse($occurrenceDate)->format('d/m/Y').' dari jadwal "'.$info.'" berhasil dibatalkan!',
            'future' => 'Jadwal "'.$info.'" dibatalkan mulai tanggal '.Carbon::parse($occurrenceDate)->format('d/m/Y').' dan seterusnya!',
            default => 'Jadwal "'.$info.'" berhasil dibatalkan tanpa menghapus histori!',
        };

        return redirect()->route('admin.schedules.index')
            ->with('success', $message);
    }

    /**
     * Move a single occurrence to a different lab/time on a specific date.
     */
    private function updateSingleOccurrence(Schedule $schedule, Request $request, array $validated)
    {
        $occurrenceDate = $request->input('occurrence_date');
        if (! $occurrenceDate) {
            return back()->withErrors(['occurrence_date' => 'Tanggal kemunculan wajib diisi.'])->withInput();
        }

        $date = Carbon::parse($occurrenceDate);
        $targetDate = Carbon::parse($request->input('target_date', $occurrenceDate));

        app(ScheduleChangeService::class)->moveOccurrence(
            $schedule,
            $date,
            $targetDate,
            (int) $validated['lab_id'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['change_reason'],
            Auth::id()
        );

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Kemunculan tanggal '.$date->format('d/m/Y').' berhasil dipindah!');
    }

    /**
     * Apply the edited values starting from a given occurrence onward:
     * truncates the current series and opens a new one at the occurrence date.
     */
    private function updateFutureOccurrences(Schedule $schedule, Request $request, array $validated)
    {
        $occurrenceDate = $request->input('occurrence_date');
        if (! $occurrenceDate) {
            return back()->withErrors(['occurrence_date' => 'Tanggal kemunculan wajib diisi.'])->withInput();
        }

        $date = Carbon::parse($occurrenceDate);
        $targetDate = Carbon::parse($request->input('target_date', $occurrenceDate));
        $newData = ScheduleService::mapFromRequest($validated, $schedule->type);
        $newSchedule = app(ScheduleChangeService::class)->changeFuture(
            $schedule,
            $date,
            $targetDate,
            $newData,
            $validated['change_reason'],
            Auth::id()
        );
        $this->saveDocumentData($newSchedule, $request);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Rangkaian jadwal diubah mulai tanggal '.$date->format('d/m/Y').' dan seterusnya!');
    }

    private function nextOccurrenceOnOrAfter(Schedule $schedule, Carbon $date): ?Carbon
    {
        $candidate = $schedule->start_date && $schedule->start_date->gt($date)
            ? $schedule->start_date->copy()
            : $date->copy();
        $calendar = app(ScheduleCalendarService::class);
        $lastDate = $schedule->end_date?->copy() ?? $candidate->copy()->addDays(6);

        while ($candidate->lte($lastDate)) {
            if ($calendar->isOccurrenceDate($schedule, $candidate)) {
                return $candidate;
            }

            $candidate->addDay();
        }

        return null;
    }

    private function nextDateForDay(Carbon $date, string $day): Carbon
    {
        $candidate = $date->copy();
        while (DayHelper::fromDate($candidate) !== $day) {
            $candidate->addDay();
        }

        return $candidate;
    }

    private function validateDayInDateRange($selectedDay, $startDate, $endDate, ?array $recurrenceDays = null)
    {
        // If no date range specified, validation passes (schedule berlaku selamanya)
        if (! $startDate && ! $endDate) {
            return true;
        }

        // If only one date is specified, use it for both start and end
        $start = Carbon::parse($startDate ?? $endDate);
        $end = Carbon::parse($endDate ?? $startDate);

        $allowedDays = app(RecurrenceDateService::class)->normaliseDays($recurrenceDays, $selectedDay);

        // The start date must be one of the selected recurring weekdays. For
        // legacy/single-day schedules this naturally falls back to `day`.
        $startDayName = DayHelper::fromDate($start);
        if (! in_array($startDayName, $allowedDays, true)) {
            $formattedStart = $start->format('d/m/Y');
            $daysLabel = implode(', ', $allowedDays);

            return "Tanggal mulai ({$formattedStart}) adalah hari {$startDayName}. Pilih tanggal yang jatuh pada salah satu hari: {$daysLabel}.";
        }

        // Check if selected day exists in the date range
        $dayFound = false;
        $currentDate = $start->copy();

        while ($currentDate->lte($end)) {
            if (in_array(DayHelper::fromDate($currentDate), $allowedDays, true)) {
                $dayFound = true;
                break;
            }
            $currentDate->addDay();
        }

        if (! $dayFound) {
            $formattedStart = $start->format('d/m/Y');
            $formattedEnd = $end->format('d/m/Y');

            return "Tidak ada hari yang dipilih dalam rentang tanggal {$formattedStart} - {$formattedEnd}. Pilih rentang yang memuat salah satu hari: ".implode(', ', $allowedDays).'.';
        }

        return true;
    }

    /**
     * Check for pending bookings that conflict with the schedule
     * Returns an error message if any pending bookings are found, null otherwise
     */
    private function checkPendingBookings($labId, $day, $startTime, $endTime, $startDate, $endDate, $recurrenceDays = null, $excludeBookingId = null, ?string $scheduleType = null)
    {
        $recurrenceDates = app(RecurrenceDateService::class);
        $days = $recurrenceDates->normaliseDays($recurrenceDays, $day);
        $rangeStart = $startDate ? Carbon::parse($startDate) : null;
        $rangeEnd = Carbon::parse($endDate ?? $startDate ?? now('Asia/Jakarta')->addYears(5));
        $requestedDates = $rangeStart && $scheduleType
            ? $recurrenceDates->datesForScheduleTypeRange(
                $scheduleType,
                $rangeStart,
                $rangeEnd,
                $recurrenceDays,
                $day,
                $rangeStart,
                $rangeEnd
            )
            : null;

        $pendingBookings = Booking::where('lab_id', $labId)
            ->where('status', 'pending')
            ->when($excludeBookingId, fn ($query) => $query->where('id', '!=', $excludeBookingId))
            ->get()
            ->filter(function (Booking $booking) use ($days, $startTime, $endTime, $rangeStart, $rangeEnd, $requestedDates, $recurrenceDates) {
                if (! $this->timesOverlap($booking->start_time, $booking->end_time, $startTime, $endTime)) {
                    return false;
                }

                $bookingDays = $recurrenceDates->normaliseDays(
                    $booking->recurrence_days,
                    $booking->day
                );

                if (! $rangeStart) {
                    return array_intersect($days, $bookingDays) !== [];
                }

                $bookingDates = $recurrenceDates->datesForBooking($booking);

                if ($requestedDates) {
                    return $bookingDates->intersect($requestedDates)->isNotEmpty();
                }

                return array_intersect($days, $bookingDays) !== []
                    && $bookingDates->contains(fn (string $date) => Carbon::parse($date)->betweenIncluded($rangeStart, $rangeEnd));
            });

        if ($pendingBookings->count() > 0) {
            $bookingList = $pendingBookings->map(function ($booking) {
                $name = $booking->course_name ?? $booking->activity_name ?? 'Peminjaman';
                $date = Carbon::parse($booking->booking_date)->format('d/m/Y');
                $time = Carbon::parse($booking->start_time)->format('H:i').' - '.Carbon::parse($booking->end_time)->format('H:i');

                return "{$name} ({$date}, {$time})";
            })->join(', ');

            return "Tidak dapat membuat jadwal. Terdapat {$pendingBookings->count()} peminjaman pending yang bentrok: {$bookingList}. Silakan tolak atau approve peminjaman tersebut terlebih dahulu.";
        }

        return null;
    }

    /**
     * Get validation rules based on schedule type
     * Eliminates duplication between store() and update()
     */
    private function getScheduleValidationRules(Request $request): array
    {
        // Base validation rules
        $rules = [
            'lab_id' => 'required|exists:labs,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'student_count' => 'required|integer|min:1',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'target_date' => 'nullable|date',
        ];

        if ($request->isMethod('PUT')) {
            $rules['scope'] = 'nullable|in:all,single,future';
            $rules['occurrence_date'] = 'nullable|required_if:scope,single,future|date';
            $rules['change_reason'] = 'required|string|max:1000';
        }

        // Conditional validation based on type
        if ($request->type === 'perkuliahan_tetap' || $request->type === 'perkuliahan_tidak_tetap') {
            $rules['course_name'] = 'required|string|max:255';
            $rules['lecturer_name'] = 'required|string|max:255';
            $rules['komting'] = 'nullable|string|max:255';
            $rules['komting_phone'] = 'nullable|string|max:20';

            if ($request->type === 'perkuliahan_tetap') {
                $rules['start_date'] = 'required|date';
                $rules['end_date'] = [
                    'required',
                    'date',
                    'after_or_equal:start_date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->start_date && Carbon::parse($request->start_date)->diffInWeeks(Carbon::parse($value)) >= 60) {
                            $fail('Jadwal berulang dibatasi maksimal 60 pertemuan.');
                        }
                    },
                ];
            } else {
                $rules['start_date'] = 'required|date';
                $rules['schedule_frequency'] = 'required|in:once,multiple';

                if ($request->schedule_frequency === 'multiple') {
                    $rules['end_date'] = [
                        'required',
                        'date',
                        'after_or_equal:start_date',
                        function ($attribute, $value, $fail) use ($request) {
                            try {
                                $days = app(RecurrenceDateService::class)->normaliseDays(
                                    $request->input('recurrence_days', []),
                                    $request->day
                                );
                                $meetings = app(RecurrenceDateService::class)->datesBetween(
                                    Carbon::parse($request->start_date),
                                    Carbon::parse($value),
                                    $days,
                                    $request->day
                                )->count();
                            } catch (\Throwable) {
                                return;
                            }

                            if ($meetings > 60) {
                                $fail('Jadwal berulang dibatasi maksimal 60 pertemuan.');
                            }
                        },
                    ];
                    $rules['recurrence_days'] = 'required|array|min:1';
                }
            }
        } elseif ($request->type === 'non_perkuliahan') {
            $rules['activity_name'] = 'required|string|max:255';
            $rules['activity_type'] = 'required|in:'.implode(',', Booking::ACTIVITY_TYPES);
            $rules['position'] = 'nullable|string|max:255';
            $rules['equipment_needs'] = 'nullable|string|max:1000';
            $rules['pic_name_non_perkuliahan'] = 'required|string|max:255';
        }

        // Document fields (optional, for all types)
        $rules['study_program'] = 'nullable|string|max:255';
        $rules['nim'] = 'nullable|string|max:50';
        $rules['nip'] = 'nullable|string|max:50';
        $rules['lecturer_nip'] = 'nullable|string|max:50';
        $rules['doc_phone_number'] = 'nullable|string|max:20';
        $rules['software_needs'] = 'nullable|string|max:1000';
        $rules['ktm_file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'; // 5MB

        return $rules;
    }

    /**
     * Apply filters to the schedule query
     */
    private function applyFilters($query, Request $request)
    {
        // Filter by Lab
        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Day (manual day selection - independent of date filter)
        if ($request->filled('day')) {
            $selectedDate = $request->filled('date') ? Carbon::parse($request->date) : null;
            $selectedDateMatchesDay = $selectedDate
                && DayHelper::fromDate($selectedDate) === $request->day;

            $query->where(function ($q) use ($request, $selectedDate, $selectedDateMatchesDay) {
                $q->where('day', $request->day)
                    ->orWhereJsonContains('recurrence_days', $request->day);

                if ($selectedDateMatchesDay) {
                    $q->orWhere(function ($activity) use ($selectedDate) {
                        $activity->where('type', 'non_perkuliahan')
                            ->whereNotNull('start_date')
                            ->whereNotNull('end_date')
                            ->whereColumn('start_date', '<', 'end_date')
                            ->whereDate('start_date', '<=', $selectedDate->toDateString())
                            ->whereDate('end_date', '>=', $selectedDate->toDateString());
                    });
                }
            });
        }

        // Filter by Search (course/activity name or lecturer)
        if ($request->filled('search')) {
            $searchTerm = '%'.$request->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('course', 'like', $searchTerm)
                    ->orWhere('lecturer', 'like', $searchTerm)
                    ->orWhere('komting', 'like', $searchTerm);
            });
        }

        // Filter by Date (specific date) - HIGHEST PRIORITY
        if ($request->filled('date')) {
            // Note: Validation handled in index method
            try {
                $date = Carbon::parse($request->date);
                $dayName = DayHelper::fromDate($date);

                $query->where(function ($q) use ($dayName, $request) {
                    $q->where('day', $dayName)
                        ->orWhereJsonContains('recurrence_days', $dayName)
                        ->orWhere(function ($activity) use ($request) {
                            $activity->where('type', 'non_perkuliahan')
                                ->whereNotNull('start_date')
                                ->whereNotNull('end_date')
                                ->whereColumn('start_date', '<', 'end_date')
                                ->whereDate('start_date', '<=', $request->date)
                                ->whereDate('end_date', '>=', $request->date);
                        });
                })
                    ->where(function ($q) use ($request) {
                        $q->where(function ($q2) use ($request) {
                            // Has start_date, check range
                            $q2->whereNotNull('start_date')
                                ->where('start_date', '<=', $request->date)
                                ->where(function ($q3) use ($request) {
                                    $q3->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $request->date);
                                });
                        })->orWhere(function ($q2) use ($request) {
                            // No start_date (recurring), check end_date only
                            $q2->whereNull('start_date')
                                ->where(function ($q3) use ($request) {
                                    $q3->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $request->date);
                                });
                        });
                    });
            } catch (\Exception $e) {
                // Ignore parser error here as it's handled in index
            }
        } elseif ($request->filled('month')) {
            // Filter by Month (broader than date) - SECOND PRIORITY
            $yearMonth = $request->month; // Format: "2026-01"
            $firstDayOfMonth = Carbon::parse($yearMonth.'-01');
            $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

            $query->where(function ($q) use ($firstDayOfMonth, $lastDayOfMonth) {
                $q->where(function ($q2) use ($firstDayOfMonth, $lastDayOfMonth) {
                    // Has start_date, check if schedule is active during this month
                    $q2->whereNotNull('start_date')
                        ->where('start_date', '<=', $lastDayOfMonth->format('Y-m-d'))
                        ->where(function ($q3) use ($firstDayOfMonth) {
                            $q3->whereNull('end_date')
                                ->orWhere('end_date', '>=', $firstDayOfMonth->format('Y-m-d'));
                        });
                })->orWhere(function ($q2) use ($firstDayOfMonth) {
                    // No start_date (recurring), check if still active in this month
                    $q2->whereNull('start_date')
                        ->where(function ($q3) use ($firstDayOfMonth) {
                            $q3->whereNull('end_date')
                                ->orWhere('end_date', '>=', $firstDayOfMonth->format('Y-m-d'));
                        });
                });
            });
        } else {
            $today = now('Asia/Jakarta')->format('Y-m-d');

            match ($request->input('period', 'upcoming')) {
                'history' => $query
                    ->whereNotNull('end_date')
                    ->where('end_date', '<', $today),
                'all' => null,
                default => $query->where(function ($q) use ($today) {
                    $q->where(function ($q2) use ($today) {
                        $q2->whereNotNull('end_date')
                            ->where('end_date', '>=', $today);
                    })->orWhereNull('end_date');
                }),
            };
        }
    }

    /**
     * Save or update document data for a schedule
     */
    private function saveDocumentData(Schedule $schedule, Request $request): void
    {
        $documentFields = [
            'study_program', 'nim', 'nip', 'lecturer_nip',
            'software_needs',
        ];

        // Only save phone to document for non_perkuliahan (perkuliahan uses schedules.komting_phone)
        $docData = $request->only($documentFields);
        if ($schedule->type === 'non_perkuliahan') {
            $docData['phone_number'] = $request->input('doc_phone_number');
        } else {
            $docData['phone_number'] = null; // Clear for perkuliahan types
        }

        // Handle KTM file upload
        if ($request->hasFile('ktm_file')) {
            // Delete old KTM if exists
            if ($schedule->document && $schedule->document->ktm_path) {
                Storage::disk('public')->delete($schedule->document->ktm_path);
            }
            $docData['ktm_path'] = $request->file('ktm_file')->store('ktm', 'public');
        }

        // Only save if any document field has a value
        $hasData = collect($docData)->filter()->isNotEmpty();
        if ($hasData || ($schedule->document && $schedule->document->exists)) {
            $schedule->document()
                ->updateOrCreate(
                    ['schedule_id' => $schedule->id],
                    $docData
                );
        }
    }

    /**
     * Print schedule document
     */
    public function print($id)
    {
        $schedule = Schedule::with(['lab', 'booking', 'document'])->findOrFail($id);

        return view('admin.schedules.print', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * Delete KTM file for a schedule
     */
    public function deleteKtm($id)
    {
        $schedule = Schedule::with('document')->findOrFail($id);

        if ($schedule->document && $schedule->document->ktm_path) {
            Storage::disk('public')->delete($schedule->document->ktm_path);
            $schedule->document->update(['ktm_path' => null]);
        }

        return back()->with('success', 'File KTM berhasil dihapus.');
    }
}
