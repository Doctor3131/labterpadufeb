<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BpsMasterData;
use App\Models\BpsSubData;
use Illuminate\Http\Request;

class BpsSubDataController extends Controller
{
    /**
     * Display a listing of sub data for a master
     */
    public function index(BpsMasterData $master)
    {
        $subData = $master->subData()->ordered()->paginate(15);

        return view('admin.bps.sub-data.index', compact('master', 'subData'));
    }

    /**
     * Show the form for creating new sub data
     */
    public function create(BpsMasterData $master)
    {
        return view('admin.bps.sub-data.create', compact('master'));
    }

    /**
     * Store a newly created sub data
     */
    public function store(Request $request, BpsMasterData $master)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['master_id'] = $master->id;
        BpsSubData::create($validated);

        return redirect()->route('admin.bps.sub-data.index', $master)
            ->with('success', 'Sub data berhasil ditambahkan!');
    }

    /**
     * Show the form for editing sub data
     */
    public function edit(BpsMasterData $master, BpsSubData $subDatum)
    {
        return view('admin.bps.sub-data.edit', compact('master', 'subDatum'));
    }

    /**
     * Update the specified sub data
     */
    public function update(Request $request, BpsMasterData $master, BpsSubData $subDatum)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $subDatum->update($validated);

        return redirect()->route('admin.bps.sub-data.index', $master)
            ->with('success', 'Sub data berhasil diperbarui!');
    }

    /**
     * Remove the specified sub data
     */
    public function destroy(BpsMasterData $master, BpsSubData $subDatum)
    {
        // Check if sub data is used in any requests
        if ($subDatum->requests()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus. Data ini sudah digunakan dalam permintaan.');
        }

        $subDatum->delete();

        return redirect()->route('admin.bps.sub-data.index', $master)
            ->with('success', 'Sub data berhasil dihapus!');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(BpsMasterData $master, BpsSubData $subDatum)
    {
        $subDatum->update(['is_active' => !$subDatum->is_active]);

        return back()->with('success', 'Status berhasil diubah!');
    }
}
