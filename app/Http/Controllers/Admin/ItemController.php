<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetUnit;
use App\Models\Batch;
use App\Models\InventoryBalance;
use App\Models\Item;
use App\Services\ItemImageService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected ItemImageService $itemImageService;

    public function __construct(ItemImageService $itemImageService)
    {
        $this->itemImageService = $itemImageService;
    }

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
            $unitsByLab = AssetUnit::whereHas('batch', function ($q) use ($item) {
                $q->where('item_id', $item->id);
            })->with(['batch', 'lab'])->get()->groupBy(function ($unit) {
                return $unit->lab->name;
            });
            $totalUnits = $unitsByLab->flatten()->count();
        } else {
            $balancesByLab = InventoryBalance::whereHas('batch', function ($q) use ($item) {
                $q->where('item_id', $item->id);
            })->where('quantity', '>', 0)->with(['batch', 'lab'])->get()->groupBy(function ($balance) {
                return $balance->lab->name;
            });
            $totalUnits = $balancesByLab->flatten()->sum('quantity');
        }

        return view('admin.items.show', compact('item', 'unitsByLab', 'balancesByLab', 'totalUnits'));
    }

    public function bulkUpdateUnitBrand(Request $request)
    {
        $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'integer|exists:asset_units,id',
            'brand' => 'nullable|string|max:100',
        ]);
        $brand = $request->input('brand') ?: null;
        $updated = AssetUnit::whereIn('id', $request->input('unit_ids'))->update(['brand' => $brand]);

        return response()->json(['success' => true, 'brand' => $brand, 'updated' => $updated]);
    }

    public function updateBatchBrand(Request $request, Batch $batch)
    {
        $request->validate(['brand' => 'nullable|string|max:100']);
        $batch->update(['brand' => $request->input('brand') ?: null]);

        return response()->json(['success' => true, 'brand' => $batch->brand]);
    }

    public function updateUnitBrand(Request $request, AssetUnit $unit)
    {
        $request->validate(['brand' => 'nullable|string|max:100']);
        $unit->update(['brand' => $request->input('brand') ?: null]);

        return response()->json(['success' => true, 'brand' => $unit->brand]);
    }

    public function updateBalanceBrand(Request $request, InventoryBalance $balance)
    {
        $request->validate(['brand' => 'nullable|string|max:100']);
        $balance->update(['brand' => $request->input('brand') ?: null]);

        return response()->json(['success' => true, 'brand' => $balance->brand]);
    }

    /**
     * Upload or replace the item's image.
     */
    public function updateImage(Request $request, Item $item)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($item->image_path) {
            $this->itemImageService->delete($item->image_path);
        }

        $item->image_path = $this->itemImageService->store($request->file('image'));
        $item->save();

        return back()->with('success', 'Gambar barang berhasil diperbarui.');
    }

    /**
     * Remove the item's image.
     */
    public function destroyImage(Item $item)
    {
        if ($item->image_path) {
            $this->itemImageService->delete($item->image_path);
            $item->image_path = null;
            $item->save();
        }

        return back()->with('success', 'Gambar barang berhasil dihapus.');
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

            // Delete the item's image file if present
            if ($item->image_path) {
                $this->itemImageService->delete($item->image_path);
            }

            // Delete all empty batches associated with this item first
            $item->batches()->delete();

            // Delete the item
            $item->delete();

            return back()->with('success', "Berhasil menghapus barang '{$itemName}' dari master data.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus barang: '.$e->getMessage());
        }
    }
}
