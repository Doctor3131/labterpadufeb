<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\AssetUnit;
use App\Models\InventoryBalance;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Show the detailed information for an item across all labs.
     */
    public function show(Item $item)
    {
        $item->load(['assetTypeCode', 'batches']);

        $unitsByLab = collect();
        $balancesByLab = collect();

        $totalUnits = 0;
        if ($item->hasIndividualUnits()) {
            $unitsByLab = AssetUnit::whereHas('batch', function($q) use ($item) {
                $q->where('item_id', $item->id);
            })->with(['batch', 'lab'])->get()->groupBy(function($unit) {
                return $unit->lab->name;
            });
            $totalUnits = $unitsByLab->flatten()->count();
        } else {
            $balancesByLab = InventoryBalance::whereHas('batch', function($q) use ($item) {
                $q->where('item_id', $item->id);
            })->where('quantity', '>', 0)->with(['batch', 'lab'])->get()->groupBy(function($balance) {
                return $balance->lab->name;
            });
            $totalUnits = $balancesByLab->flatten()->sum('quantity');
        }

        return view('admin.items.show', compact('item', 'unitsByLab', 'balancesByLab', 'totalUnits'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        try {
            // Check if the item has any existing asset units (for individual tracking modes)
            $hasUnits = AssetUnit::whereHas('batch', function ($query) use ($item) {
                $query->where('item_id', $item->id);
            })->exists();

            // Check if the item has any inventory balances > 0 (for aggregate tracking mode)
            $hasBalances = InventoryBalance::whereHas('batch', function ($query) use ($item) {
                $query->where('item_id', $item->id);
            })->where('quantity', '>', 0)->exists();

            if ($hasUnits || $hasBalances) {
                return back()->with('error', 'Gagal menghapus barang: Barang ini masih memiliki stok atau riwayat transaksi di dalam inventaris.');
            }

            $itemName = $item->name;
            
            // Delete all empty batches associated with this item first
            $item->batches()->delete();
            
            // Delete the item
            $item->delete();

            return back()->with('success', "Berhasil menghapus barang '{$itemName}' dari master data.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }
}
