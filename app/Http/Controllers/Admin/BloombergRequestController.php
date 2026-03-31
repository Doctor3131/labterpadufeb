<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloombergRequest;
use App\Models\BlockedDate;
use App\Models\ServiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BloombergRequestController extends Controller
{
    /**
     * Display a listing of Bloomberg requests, filtered by type.
     */
    public function index(Request $request)
    {
        $type = in_array($request->get('type'), ['reservasi', 'walk_in', 'all']) 
            ? $request->get('type') 
            : 'all';

        $query = BloombergRequest::orderBy('usage_date', 'desc')
            ->orderBy('session', 'asc');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $requests = $query->paginate(15);

        // Get counts for tabs
        $counts = [
            'reservasi' => BloombergRequest::where('type', 'reservasi')->count(),
            'walk_in' => BloombergRequest::where('type', 'walk_in')->count(),
            'all' => BloombergRequest::count(),
        ];

        return view('admin.bloomberg.index', compact('requests', 'type', 'counts'));
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

        return view('admin.bloomberg.blocked-dates', compact('blockedDates'));
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
