<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\Request;

class LabController extends Controller
{
    /**
     * Display a listing of the labs.
     */
    public function index()
    {
        $labs = Lab::withCount(['schedules', 'bookings'])->orderBy('name')->get();
        return view('admin.labs.index', compact('labs'));
    }

    /**
     * Show the form for creating a new lab.
     */
    public function create()
    {
        return view('admin.labs.create');
    }

    /**
     * Store a newly created lab in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Auto-generate code from name
        $validated['code'] = str_replace([' ', '.'], '-', $validated['name']);
        
        Lab::create($validated);

        return redirect()->route('admin.labs.index')
            ->with('success', 'Lab berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified lab.
     */
    public function edit(Lab $lab)
    {
        return view('admin.labs.edit', compact('lab'));
    }

    /**
     * Update the specified lab in storage.
     */
    public function update(Request $request, Lab $lab)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Auto-generate code from name
        $validated['code'] = str_replace([' ', '.'], '-', $validated['name']);
        
        $lab->update($validated);

        return redirect()->route('admin.labs.index')
            ->with('success', 'Lab berhasil diperbarui!');
    }

    /**
     * Remove the specified lab from storage.
     */
    public function destroy(Lab $lab)
    {
        // Check if lab has schedules or bookings
        if ($lab->schedules()->count() > 0 || $lab->bookings()->count() > 0) {
            return redirect()->route('admin.labs.index')
                ->with('error', 'Lab tidak dapat dihapus karena memiliki jadwal atau booking aktif!');
        }

        $lab->delete();

        return redirect()->route('admin.labs.index')
            ->with('success', 'Lab berhasil dihapus!');
    }

    /**
     * Toggle lab status (enable/disable for booking)
     */
    public function toggleStatus(Lab $lab)
    {
        $newStatus = $lab->status === 'available' ? 'inactive' : 'available';
        
        $lab->update(['status' => $newStatus]);

        $message = $newStatus === 'available' 
            ? 'Lab berhasil diaktifkan!' 
            : 'Lab berhasil dinonaktifkan!';

        return redirect()->route('admin.labs.index')
            ->with('success', $message);
    }
}
