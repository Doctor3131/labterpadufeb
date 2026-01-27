<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Store a new feedback from user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'detail' => 'required|string|max:5000',
        ]);

        Feedback::create([
            'title' => $validated['title'],
            'detail' => $validated['detail'],
            'status' => 'pending',
        ]);

        return redirect()->route('landing')->with('success', '✅ Laporan Anda telah dikirim. Tim kami akan meninjau laporan Anda. Terima kasih!');
    }

    /**
     * Display all feedbacks for admin
     */
    public function index()
    {
        $feedbacks = Feedback::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.feedbacks.index', compact('feedbacks'));
    }

    /**
     * Update feedback status and admin notes
     */
    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $feedback->update($validated);

        return redirect()->back()->with('success', 'Feedback berhasil diupdate');
    }
}
