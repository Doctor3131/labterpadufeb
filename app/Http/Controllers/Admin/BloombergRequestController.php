<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloombergRequest;
use App\Models\BlockedDate;
use App\Models\ServiceSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BloombergRequestController extends Controller
{
    /** Display a searchable, filterable listing of Bloomberg requests. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:reservasi,walk_in,all'],
            'q' => ['nullable', 'string', 'max:100'],
            'applicant_type' => ['nullable', 'in:all,'.implode(',', array_keys(BloombergRequest::APPLICANT_TYPES))],
            'purpose' => ['nullable', 'in:all,'.implode(',', array_keys(BloombergRequest::PURPOSES))],
            'period' => ['nullable', 'in:all,upcoming,today,past'],
        ]);

        $type = $filters['type'] ?? 'all';
        $search = trim($filters['q'] ?? '');
        $applicantType = $filters['applicant_type'] ?? 'all';
        $purpose = $filters['purpose'] ?? 'all';
        $period = $filters['period'] ?? 'all';
        $today = today()->toDateString();

        $activeFilters = array_filter([
            'q' => $search !== '' ? $search : null,
            'applicant_type' => $applicantType !== 'all' ? $applicantType : null,
            'purpose' => $purpose !== 'all' ? $purpose : null,
            'period' => $period !== 'all' ? $period : null,
        ], static fn ($value) => $value !== null);

        $query = BloombergRequest::orderBy('usage_date', 'desc')
            ->orderBy('session', 'asc');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $term = "%{$search}%";
            $matchingApplicantTypes = array_keys(array_filter(
                BloombergRequest::APPLICANT_TYPES,
                static fn (string $label): bool => Str::contains(Str::lower($label), Str::lower($search)),
            ));
            $matchingPurposes = array_keys(array_filter(
                BloombergRequest::PURPOSES,
                static fn (string $label): bool => Str::contains(Str::lower($label), Str::lower($search)),
            ));

            $query->where(static function (Builder $searchQuery) use ($search, $term, $matchingApplicantTypes, $matchingPurposes): void {
                $searchQuery
                    ->where('name', 'like', $term)
                    ->orWhere('nim_nip', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('university', 'like', $term)
                    ->orWhere('study_program', 'like', $term)
                    ->orWhere('purpose', 'like', $term)
                    ->orWhere('purpose_other', 'like', $term)
                    ->orWhere('research_title', 'like', $term)
                    ->orWhere('subject_name', 'like', $term)
                    ->orWhere('lecturer_name', 'like', $term)
                    ->orWhere('variables', 'like', $term);

                if ($matchingApplicantTypes !== []) {
                    $searchQuery->orWhereIn('applicant_type', $matchingApplicantTypes);
                }

                if ($matchingPurposes !== []) {
                    $searchQuery->orWhereIn('purpose', $matchingPurposes);
                }

                if (ctype_digit($search)) {
                    $searchQuery->orWhere('id', (int) $search);
                }
            });
        }

        if ($applicantType !== 'all') {
            $query->where('applicant_type', $applicantType);
        }

        if ($purpose !== 'all') {
            $query->where('purpose', $purpose);
        }

        match ($period) {
            'upcoming' => $query->whereDate('usage_date', '>=', $today),
            'today' => $query->whereDate('usage_date', $today),
            'past' => $query->whereDate('usage_date', '<', $today),
            default => null,
        };

        $requests = $query->paginate(15)->withQueryString();

        // Get counts for tabs
        $counts = [
            'reservasi' => BloombergRequest::where('type', 'reservasi')->count(),
            'walk_in' => BloombergRequest::where('type', 'walk_in')->count(),
            'all' => BloombergRequest::count(),
        ];

        $hasActiveFilters = $activeFilters !== [];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.bloomberg.partials.results', compact(
                    'requests', 'type', 'search', 'applicantType', 'purpose', 'period', 'hasActiveFilters',
                ))->render(),
                'total' => $requests->total(),
            ]);
        }

        return view('admin.bloomberg.index', compact(
            'requests', 'type', 'counts', 'search', 'applicantType', 'purpose', 'period', 'hasActiveFilters',
        ));
    }

    /**
     * Display the specified Bloomberg request.
     */
    public function show(BloombergRequest $request)
    {
        return view('admin.bloomberg.show', compact('request'));
    }

    /**
     * Display blocked dates management page.
     */
    public function blockedDates()
    {
        $blockedDates = BlockedDate::forService('bloomberg')
            ->with('creator')
            ->orderBy('blocked_date', 'asc')
            ->get();

        return view('admin.bloomberg.blocked-dates', [
            'blockedDates' => $blockedDates,
            'sessions' => BloombergRequest::SESSIONS,
            'sessionsFriday' => BloombergRequest::SESSIONS_FRIDAY,
        ]);
    }

    /**
     * Add a new blocked date.
     */
    public function addBlockedDate(Request $request)
    {
        $validated = $request->validate([
            'blocked_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
            'blocked_session' => 'nullable|in:sesi_1,sesi_2',
        ], [
            'blocked_date.required' => 'Tanggal wajib diisi.',
            'blocked_date.after_or_equal' => 'Tanggal harus hari ini atau setelahnya.',
            'reason.required' => 'Alasan blokir wajib diisi.',
        ]);

        $blockedSession = $validated['blocked_session'] ?? null;

        // Check for duplicate: same date + same session (or null which means all)
        $exists = BlockedDate::where('service_type', 'bloomberg')
            ->where('blocked_date', $validated['blocked_date'])
            ->where(function ($q) use ($blockedSession) {
                if ($blockedSession) {
                    // Check if entire day is already blocked, or this specific session
                    $q->whereNull('blocked_session')
                      ->orWhere('blocked_session', $blockedSession);
                } else {
                    // Blocking all sessions: check if any block exists for this date
                    $q->whereNull('blocked_session');
                }
            })
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Tanggal/sesi tersebut sudah diblokir.');
        }

        // If blocking all sessions (null), remove any session-specific blocks for this date
        if (!$blockedSession) {
            BlockedDate::where('service_type', 'bloomberg')
                ->where('blocked_date', $validated['blocked_date'])
                ->whereNotNull('blocked_session')
                ->delete();
        }

        BlockedDate::create([
            'service_type' => 'bloomberg',
            'blocked_date' => $validated['blocked_date'],
            'reason' => $validated['reason'],
            'blocked_session' => $blockedSession,
            'created_by' => Auth::id(),
        ]);

        $sessionLabel = $blockedSession ? ($blockedSession === 'sesi_1' ? ' (Sesi 1)' : ' (Sesi 2)') : ' (Semua Sesi)';
        return redirect()->back()->with('success', 'Tanggal berhasil diblokir' . $sessionLabel . '.');
    }

    /**
     * Remove a blocked date.
     */
    public function removeBlockedDate(BlockedDate $blockedDate)
    {
        if ($blockedDate->service_type !== 'bloomberg') {
            abort(404);
        }

        $blockedDate->delete();

        return redirect()->back()->with('success', 'Tanggal blokir berhasil dihapus.');
    }

    /**
     * Display settings page.
     */
    public function settings()
    {
        $capacity = ServiceSetting::getValue('bloomberg', 'capacity_per_session', '12');
        $walkInEnabled = ServiceSetting::isEnabled('bloomberg', 'walk_in_enabled');

        return view('admin.bloomberg.settings', compact('capacity', 'walkInEnabled'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'capacity_per_session' => 'required|integer|min:1|max:100',
            'walk_in_enabled' => 'required|in:0,1',
        ], [
            'capacity_per_session.required' => 'Kapasitas wajib diisi.',
            'capacity_per_session.min' => 'Kapasitas minimal 1.',
            'capacity_per_session.max' => 'Kapasitas maksimal 100.',
        ]);

        ServiceSetting::setValue('bloomberg', 'capacity_per_session', $validated['capacity_per_session'], Auth::id());
        ServiceSetting::setValue('bloomberg', 'walk_in_enabled', $validated['walk_in_enabled'], Auth::id());

        return redirect()->back()->with('success', 'Pengaturan Bloomberg berhasil disimpan.');
    }
}
