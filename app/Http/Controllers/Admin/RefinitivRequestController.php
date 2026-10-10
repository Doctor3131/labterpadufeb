<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefinitivRequest;
use App\Services\RefinitivAttendanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RefinitivRequestController extends Controller
{
    public function __construct(private readonly RefinitivAttendanceService $attendanceService) {}

    /**
     * Display a listing of Refinitiv requests.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,pending,hadir,tidak_hadir'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:schedule_asc,schedule_desc,recent,name_asc'],
            'period' => ['nullable', 'in:all,today,next_7_days,overdue'],
            'month' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'calendar_fragment' => ['sometimes', 'boolean'],
        ]);

        $status = $filters['status'] ?? 'pending';
        $search = trim($filters['q'] ?? '');
        $sort = $filters['sort'] ?? 'schedule_asc';
        $period = $filters['period'] ?? 'all';
        $date = $filters['date'] ?? '';
        $calendarMonth = $filters['month'] ?? ($date !== '' ? substr($date, 0, 7) : now()->format('Y-m'));

        if ($date !== '' && substr($date, 0, 7) !== $calendarMonth) {
            $calendarMonth = substr($date, 0, 7);
        }

        $today = today()->toDateString();
        $tomorrow = today()->copy()->addDay()->toDateString();
        $weekEndExclusive = today()->copy()->addDays(7)->toDateString();
        $selectedDateEndExclusive = $date !== '' ? Carbon::parse($date, config('app.timezone'))->addDay()->toDateString() : null;
        $includeCalendar = ! $request->ajax() || $request->boolean('calendar_fragment');

        if ($includeCalendar) {
            $calendarMonthStart = Carbon::parse($calendarMonth.'-01', config('app.timezone'))->startOfMonth();
            $calendarMonthEndExclusive = $calendarMonthStart->copy()->addMonth()->toDateString();

            $calendarRows = RefinitivRequest::query()
                ->where('usage_date', '>=', $calendarMonthStart->toDateString())
                ->where('usage_date', '<', $calendarMonthEndExclusive)
                ->select('usage_date', 'session', 'attendance_status')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('usage_date', 'session', 'attendance_status')
                ->get();

            $calendarDays = [];
            $sessionTotals = array_fill_keys(array_keys(RefinitivRequest::SESSIONS), 0);

            foreach ($calendarRows as $row) {
                $day = Carbon::parse($row->usage_date)->toDateString();
                $session = $row->session;
                $attendanceStatus = $row->attendance_status;
                $total = (int) $row->total;

                $calendarDays[$day] ??= [
                    'total' => 0,
                    'statuses' => array_fill_keys(array_keys(RefinitivRequest::ATTENDANCE_STATUSES), 0),
                    'sessions' => [],
                ];
                $calendarDays[$day]['sessions'][$session] ??= [
                    'total' => 0,
                    'statuses' => array_fill_keys(array_keys(RefinitivRequest::ATTENDANCE_STATUSES), 0),
                ];
                $calendarDays[$day]['sessions'][$session]['total'] += $total;
                $calendarDays[$day]['total'] += $total;
                $sessionTotals[$session] = ($sessionTotals[$session] ?? 0) + $total;

                if (array_key_exists($attendanceStatus, RefinitivRequest::ATTENDANCE_STATUSES)) {
                    $calendarDays[$day]['sessions'][$session]['statuses'][$attendanceStatus] += $total;
                    $calendarDays[$day]['statuses'][$attendanceStatus] += $total;
                }
            }

            $calendarSummary = [
                'total' => array_sum(array_column($calendarDays, 'total')),
                'active_days' => count($calendarDays),
                'busiest_session' => null,
                'busiest_session_total' => 0,
                'session_totals' => $sessionTotals,
            ];

            if ($calendarSummary['total'] > 0) {
                $calendarSummary['busiest_session'] = array_search(max($sessionTotals), $sessionTotals, true);
                $calendarSummary['busiest_session_total'] = max($sessionTotals);
            }

            $calendarMonthLabel = $calendarMonthStart->locale('id')->isoFormat('MMMM Y');
            $calendarCells = array_merge(
                array_fill(0, $calendarMonthStart->isoWeekday() - 1, null),
                range(1, $calendarMonthStart->daysInMonth),
            );
            while (count($calendarCells) % 7 !== 0) {
                $calendarCells[] = null;
            }

            $calendarToday = today()->toDateString();
            $calendarSelectedDate = $date !== ''
                ? $date
                : (str_starts_with($calendarToday, $calendarMonth) ? $calendarToday : $calendarMonthStart->toDateString());
            $emptyCalendarDay = [
                'total' => 0,
                'statuses' => array_fill_keys(array_keys(RefinitivRequest::ATTENDANCE_STATUSES), 0),
                'sessions' => [],
            ];
            $calendarSelectedDay = $calendarDays[$calendarSelectedDate] ?? $emptyCalendarDay;
            foreach (array_keys(RefinitivRequest::SESSIONS) as $session) {
                $calendarSelectedDay['sessions'][$session] ??= [
                    'total' => 0,
                    'statuses' => array_fill_keys(array_keys(RefinitivRequest::ATTENDANCE_STATUSES), 0),
                ];
            }
            $calendarSessionNames = array_map(fn (string $label): string => Str::before($label, ':'), RefinitivRequest::SESSIONS);
            $calendarSessionTimes = [
                'sesi_1' => '08.00–10.00 WIB',
                'sesi_2' => '10.00–12.00 WIB',
                'sesi_3' => Carbon::parse($calendarSelectedDate, config('app.timezone'))->isFriday() ? '13.30–15.30 WIB' : '13.00–15.00 WIB',
            ];
            $previousCalendarMonth = $calendarMonthStart->copy()->subMonth()->format('Y-m');
            $nextCalendarMonth = $calendarMonthStart->copy()->addMonth()->format('Y-m');
        }

        $baseQuery = RefinitivRequest::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $term = "%{$search}%";

                    $searchQuery
                        ->where('name', 'like', $term)
                        ->orWhere('nim_nip', 'like', $term)
                        ->orWhere('whatsapp', 'like', $term)
                        ->orWhere('purpose', 'like', $term)
                        ->orWhere('purpose_other', 'like', $term)
                        ->orWhere('variables', 'like', $term)
                        ->orWhere('lecturer_name', 'like', $term)
                        ->orWhere('study_program', 'like', $term)
                        ->orWhere('affiliation', 'like', $term)
                        ->orWhere('applicant_type', 'like', $term)
                        ->orWhere('session', 'like', $term);

                    $matchingAffiliations = array_keys(array_filter(
                        RefinitivRequest::AFFILIATIONS,
                        fn (string $label): bool => Str::contains(Str::lower($label), Str::lower($search)),
                    ));
                    $matchingPurposes = array_keys(array_filter(
                        RefinitivRequest::PURPOSES,
                        fn (string $label): bool => Str::contains(Str::lower($label), Str::lower($search)),
                    ));
                    $matchingSessions = array_keys(array_filter(
                        RefinitivRequest::SESSIONS,
                        fn (string $label): bool => Str::contains(Str::lower($label), Str::lower($search)),
                    ));

                    if ($matchingAffiliations !== []) {
                        $searchQuery->orWhereIn('affiliation', $matchingAffiliations);
                    }
                    if ($matchingPurposes !== []) {
                        $searchQuery->orWhereIn('purpose', $matchingPurposes);
                    }
                    if ($matchingSessions !== []) {
                        $searchQuery->orWhereIn('session', $matchingSessions);
                    }

                    if (ctype_digit($search)) {
                        $searchQuery->orWhere('id', (int) $search);
                    }
                });
            })
            ->when($date !== '', fn (Builder $query) => $query
                ->where('usage_date', '>=', $date)
                ->where('usage_date', '<', $selectedDateEndExclusive))
            ->when($date === '' && $period === 'today', fn (Builder $query) => $query->where('usage_date', '>=', $today)->where('usage_date', '<', $tomorrow))
            ->when($date === '' && $period === 'next_7_days', fn (Builder $query) => $query->where('usage_date', '>=', $today)->where('usage_date', '<', $weekEndExclusive))
            ->when($date === '' && $period === 'overdue', fn (Builder $query) => $query->where('usage_date', '<', $today));

        $countRows = (clone $baseQuery)
            ->select('attendance_status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('attendance_status')
            ->pluck('aggregate', 'attendance_status');

        $counts = [
            'all' => (int) $countRows->sum(),
            'pending' => (int) ($countRows['pending'] ?? 0),
            'hadir' => (int) ($countRows['hadir'] ?? 0),
            'tidak_hadir' => (int) ($countRows['tidak_hadir'] ?? 0),
        ];

        $query = (clone $baseQuery)->with('handler');

        if ($status !== 'all') {
            $query->where('attendance_status', $status);
        }

        match ($sort) {
            'schedule_desc' => $query->orderByDesc('usage_date')
                ->orderByDesc('session')
                ->orderByDesc('id'),
            'recent' => $query->orderByDesc('created_at')
                ->orderByDesc('id'),
            'name_asc' => $query->orderBy('name')
                ->orderBy('id'),
            default => $query->orderByRaw('CASE WHEN usage_date < ? THEN 1 ELSE 0 END', [today()->toDateString()])
                ->orderByRaw('CASE WHEN usage_date >= ? THEN usage_date END ASC', [today()->toDateString()])
                ->orderByRaw('CASE WHEN usage_date < ? THEN usage_date END DESC', [today()->toDateString()])
                ->orderBy('session')
                ->orderBy('id'),
        };

        $requests = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            $response = [
                'html' => view('admin.refinitiv.partials.results', compact('requests', 'status', 'search', 'sort', 'period', 'date', 'calendarMonth'))->render(),
                'total' => $requests->total(),
                'counts' => $counts,
            ];

            if ($includeCalendar) {
                $response['calendarHtml'] = view('admin.refinitiv.partials.calendar', compact(
                    'status', 'search', 'sort', 'period', 'date', 'calendarMonth', 'calendarDays', 'calendarSummary',
                    'calendarMonthLabel', 'calendarCells', 'calendarSelectedDate', 'calendarSelectedDay',
                    'calendarSessionNames', 'calendarSessionTimes', 'calendarToday', 'previousCalendarMonth',
                    'nextCalendarMonth',
                ))->render();
            }

            return response()->json($response);
        }

        return view('admin.refinitiv.index', compact(
            'requests', 'status', 'counts', 'search', 'sort', 'period', 'date', 'calendarMonth',
            'calendarDays', 'calendarSummary', 'calendarMonthLabel', 'calendarCells', 'calendarSelectedDate',
            'calendarSelectedDay', 'calendarSessionNames', 'calendarSessionTimes', 'calendarToday', 'previousCalendarMonth',
            'nextCalendarMonth',
        ));
    }

    /**
     * Display the specified Refinitiv request.
     */
    public function show(RefinitivRequest $request)
    {
        $request->load(['handler', 'attendanceEvents.actor']);

        return view('admin.refinitiv.show', compact('request'));
    }

    /**
     * Mark attendance as "Hadir".
     */
    public function markHadir(Request $httpRequest, RefinitivRequest $request): RedirectResponse
    {
        $validated = $httpRequest->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $changed = $this->attendanceService->changeStatus($request, 'hadir', (int) Auth::id(), $validated['note'] ?? null);

        return $this->attendanceResponse($changed, 'Status kehadiran berhasil diubah menjadi Hadir.', 'Pemohon sudah berstatus Hadir; tidak ada perubahan.');
    }

    /**
     * Mark attendance as "Tidak Hadir".
     */
    public function markTidakHadir(Request $httpRequest, RefinitivRequest $request): RedirectResponse
    {
        $validated = $httpRequest->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $changed = $this->attendanceService->changeStatus($request, 'tidak_hadir', (int) Auth::id(), $validated['note'] ?? null);

        return $this->attendanceResponse($changed, 'Status kehadiran berhasil diubah menjadi Tidak Hadir.', 'Pemohon sudah berstatus Tidak Hadir; tidak ada perubahan.');
    }

    /**
     * Reset attendance status to pending.
     */
    public function resetStatus(Request $httpRequest, RefinitivRequest $request): RedirectResponse
    {
        $validated = $httpRequest->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $changed = $this->attendanceService->changeStatus($request, 'pending', (int) Auth::id(), $validated['note'] ?? null);

        return $this->attendanceResponse($changed, 'Status kehadiran dikembalikan ke Menunggu.', 'Pemohon sudah berstatus Menunggu; tidak ada perubahan.');
    }

    public function markManyHadir(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:15'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:refinitiv_requests,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $count = $this->attendanceService->markManyPresent(
            array_map('intval', $validated['ids']),
            (int) Auth::id(),
            $validated['note'] ?? null,
        );

        return redirect()->back()->with('success', "{$count} permohonan berhasil ditandai Hadir.");
    }

    private function attendanceResponse(bool $changed, string $success, string $unchanged): RedirectResponse
    {
        return redirect()->back()->with($changed ? 'success' : 'info', $changed ? $success : $unchanged);
    }
}
