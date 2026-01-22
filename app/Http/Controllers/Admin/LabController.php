<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LabController extends Controller
{
    /**
     * Display a listing of the labs.
     */
    public function index()
    {
        $labs = Lab::withCount(['schedules', 'bookings'])->orderBy('code')->get();
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
            'code' => 'required|string|max:50|unique:labs,code',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('labs', 'public');
        }

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
            'code' => 'required|string|max:50|unique:labs,code,' . $lab->id,
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($lab->image) {
                Storage::disk('public')->delete($lab->image);
            }
            $validated['image'] = $request->file('image')->store('labs', 'public');
        }

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

        // Delete image if exists
        if ($lab->image) {
            Storage::disk('public')->delete($lab->image);
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
        $newStatus = $lab->status === 'available' ? 'maintenance' : 'available';
        
        $lab->update(['status' => $newStatus]);

        $message = $newStatus === 'available' 
            ? 'Lab berhasil diaktifkan!' 
            : 'Lab berhasil dinonaktifkan!';

        return redirect()->route('admin.labs.index')
            ->with('success', $message);
    }
}
