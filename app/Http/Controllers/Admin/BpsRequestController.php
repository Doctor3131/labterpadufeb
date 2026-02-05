<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BpsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BpsRequestController extends Controller
{
    /**
     * Display all BPS requests (admin dashboard view)
     */
    public function index()
    {
        $pendingRequests = BpsRequest::with(['subData.master', 'handler'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'pending_page');

        $completedRequests = BpsRequest::with(['subData.master', 'handler'])
            ->completed()
            ->orderBy('completed_at', 'desc')
            ->paginate(15, ['*'], 'completed_page');

        return view('admin.bps.requests.index', compact('pendingRequests', 'completedRequests'));
    }

    /**
     * Show request detail
     */
    public function show(BpsRequest $request)
    {
        $request->load(['subData.master', 'variables.subData', 'handler']);
        
        return view('admin.bps.requests.show', ['bpsRequest' => $request]);
    }

    /**
     * Mark request as completed (data sent)
     */
    public function markCompleted(BpsRequest $request)
    {
        $request->update([
            'status' => 'completed',
            'completed_at' => now(),
            'handled_by' => Auth::id(),
        ]);

        return back()->with('success', 'Permintaan data berhasil ditandai selesai!');
    }

    /**
     * Add admin notes
     */
    public function updateNotes(Request $req, BpsRequest $request)
    {
        $validated = $req->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $request->update($validated);

        return back()->with('success', 'Catatan berhasil disimpan!');
    }
}
