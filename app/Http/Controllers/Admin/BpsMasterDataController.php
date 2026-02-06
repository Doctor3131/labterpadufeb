<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BpsMasterData;
use App\Models\BpsSubData;
use Illuminate\Http\Request;

class BpsMasterDataController extends Controller
{
    /**
     * Display a listing of master data
     */
    public function index()
    {
        $masterData = BpsMasterData::withCount('subData')
            ->ordered()
            ->paginate(15);

        return view('admin.bps.master-data.index', compact('masterData'));
    }

    /**
     * Show the form for creating new master data
     */
    public function create()
    {
        return view('admin.bps.master-data.create');
    }

    /**
     * Store a newly created master data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:bps_master_data,code',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'has_sub_data' => 'boolean',
        ]);

        BpsMasterData::create($validated);

        return redirect()->route('admin.bps.master-data.index')
            ->with('success', 'Master data berhasil ditambahkan!');
    }

    /**
     * Show the form for editing master data
     */
    public function edit(BpsMasterData $masterDatum)
    {
        return view('admin.bps.master-data.edit', ['masterData' => $masterDatum]);
    }

    /**
     * Update the specified master data
     */
    public function update(Request $request, BpsMasterData $masterDatum)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:bps_master_data,code,' . $masterDatum->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'has_sub_data' => 'boolean',
        ]);

        $masterDatum->update($validated);

        return redirect()->route('admin.bps.master-data.index')
            ->with('success', 'Master data berhasil diperbarui!');
    }

    /**
     * Remove the specified master data
     */
    public function destroy(BpsMasterData $masterDatum)
    {
        // Check if there are related sub data
        if ($masterDatum->subData()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus. Hapus semua sub data terlebih dahulu.');
        }

        $masterDatum->delete();

        return redirect()->route('admin.bps.master-data.index')
            ->with('success', 'Master data berhasil dihapus!');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(BpsMasterData $masterDatum)
    {
        $masterDatum->update(['is_active' => !$masterDatum->is_active]);

        return back()->with('success', 'Status berhasil diubah!');
    }
}
