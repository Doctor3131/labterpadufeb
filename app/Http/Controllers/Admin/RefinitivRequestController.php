<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefinitivRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefinitivRequestController extends Controller
{
    /**
     * Display a listing of Refinitiv requests.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $query = RefinitivRequest::with('handler')
            ->orderBy('usage_date', 'asc')
            ->orderBy('session', 'asc');
        
        if ($status !== 'all') {
            $query->where('attendance_status', $status);
        }
        
        $requests = $query->paginate(15);
        
        // Get counts for tabs
        $counts = [
            'pending' => RefinitivRequest::where('attendance_status', 'pending')->count(),
            'hadir' => RefinitivRequest::where('attendance_status', 'hadir')->count(),
            'tidak_hadir' => RefinitivRequest::where('attendance_status', 'tidak_hadir')->count(),
        ];
        
        return view('admin.refinitiv.index', compact('requests', 'status', 'counts'));
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

    /**
     * Update admin notes.
     */
    public function updateNotes(Request $request, RefinitivRequest $refinitivRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $refinitivRequest->update([
            'admin_notes' => $validated['admin_notes'],
        ]);
        
        return redirect()->back()->with('success', 'Catatan berhasil disimpan.');
    }
}
