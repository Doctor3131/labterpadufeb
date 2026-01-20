<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventories = Inventory::latest()->paginate(10);
        return view('admin.inventories.index', compact('inventories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.inventories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|unique:inventories,item_code|max:255',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'condition' => 'required|string|in:Baik,Rusak,Perbaikan',
            'price' => 'required|numeric|min:0',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Inventory::create($validated);

        return redirect()->route('admin.inventories.index')
            ->with('success', 'Barang berhasil ditambahkan ke inventaris.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        return view('admin.inventories.edit', compact('inventory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'item_code' => 'required|max:255|unique:inventories,item_code,' . $inventory->id,
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'condition' => 'required|string|in:Baik,Rusak,Perbaikan',
            'price' => 'required|numeric|min:0',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $inventory->update($validated);

        return redirect()->route('admin.inventories.index')
            ->with('success', 'Data inventaris berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->route('admin.inventories.index')
            ->with('success', 'Barang berhasil dihapus dari inventaris.');
    }
}
