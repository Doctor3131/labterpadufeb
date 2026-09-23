<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefinitivRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefinitivRequestController extends Controller
{
    /**
     * Display a listing of Refinitiv requests.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,pending,hadir,tidak_hadir'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:schedule_asc,schedule_desc,recent,name_asc'],
        ]);

        $status = $filters['status'] ?? 'pending';
        $search = trim($filters['q'] ?? '');
        $sort = $filters['sort'] ?? 'schedule_asc';

        $query = RefinitivRequest::with('handler')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $term = "%{$search}%";

                    $searchQuery
                        ->where('name', 'like', $term)
                        ->orWhere('nim_nip', 'like', $term)
                        ->orWhere('whatsapp', 'like', $term);

                    if (ctype_digit($search)) {
                        $searchQuery->orWhere('id', (int) $search);
                    }
                });
            });

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
            return response()->json([
                'html' => view('admin.refinitiv.partials.results', compact('requests', 'status', 'search', 'sort'))->render(),
                'total' => $requests->total(),
            ]);
        }

        // These counts are only needed for the initial page; AJAX filtering keeps the existing tabs in place.
        $counts = [
            'all' => RefinitivRequest::count(),
            'pending' => RefinitivRequest::where('attendance_status', 'pending')->count(),
            'hadir' => RefinitivRequest::where('attendance_status', 'hadir')->count(),
            'tidak_hadir' => RefinitivRequest::where('attendance_status', 'tidak_hadir')->count(),
        ];

        return view('admin.refinitiv.index', compact('requests', 'status', 'counts', 'search', 'sort'));
    }

    /**
     * Display the specified Refinitiv request.
     */
    public function show(RefinitivRequest $request)
    {
        return view('admin.refinitiv.show', compact('request'));
    }

    /**
     * Mark attendance as "Hadir".
     */
    public function markHadir(RefinitivRequest $request)
    {
        $request->update([
            'attendance_status' => 'hadir',
            'attendance_marked_at' => now(),
            'handled_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Status kehadiran berhasil diubah menjadi HADIR.');
    }

    /**
     * Mark attendance as "Tidak Hadir".
     */
    public function markTidakHadir(RefinitivRequest $request)
    {
        $request->update([
            'attendance_status' => 'tidak_hadir',
            'attendance_marked_at' => now(),
            'handled_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Status kehadiran berhasil diubah menjadi TIDAK HADIR.');
    }

    /**
     * Reset attendance status to pending.
     */
    public function resetStatus(RefinitivRequest $request)
    {
        $request->update([
            'attendance_status' => 'pending',
            'attendance_marked_at' => null,
            'handled_by' => null,
        ]);

        return redirect()->back()->with('success', 'Status kehadiran berhasil direset.');
    }
}
