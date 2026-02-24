<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\Item;
use App\Models\AssetUnit;
use App\Models\InventoryBalance;
use App\Services\InventoryService;
use App\Enums\ConditionEnum;
use Illuminate\Http\Request;

class InventoryTransferController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function create(Request $request)
    {
        $labs = Lab::orderBy('name')->get();
        $sourceLabId = $request->query('source_lab_id');
        
        return view('admin.inventory.transfer.create', compact('labs', 'sourceLabId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_lab_id' => 'required|exists:labs,id',
            'target_lab_id' => 'required|exists:labs,id|different:source_lab_id',
            'item_id' => 'required|exists:items,id',
            'tracking_mode' => 'required|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $sourceLabId = $request->source_lab_id;
        $targetLabId = $request->target_lab_id;
        $notes = $request->notes;

        try {
            if ($request->tracking_mode !== 'AGGREGATE') {
                $request->validate([
                    'unit_ids' => 'required|array|min:1',
                    'unit_ids.*' => 'exists:asset_units,id'
                ]);

                $this->inventoryService->transferUnitsToLab($request->unit_ids, $targetLabId, $notes);
            } else {
                $request->validate([
                    'transfers' => 'required|array|min:1',
                    'transfers.*.batch_id' => 'required|exists:batches,id',
                    'transfers.*.condition' => 'required|string',
                    'transfers.*.quantity' => 'nullable|integer|min:0',
                ]);

                $transferredAny = false;
                foreach ($request->transfers as $transfer) {
                    if (empty($transfer['quantity']) || $transfer['quantity'] <= 0) continue;
                    
                    $this->inventoryService->transferAggregateToLab(
                        $sourceLabId,
                        $targetLabId,
                        $transfer['batch_id'],
                        ConditionEnum::from($transfer['condition']),
                        $transfer['quantity'],
                        $notes
                    );
                    $transferredAny = true;
                }

                if (!$transferredAny) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Silakan isi jumlah barang yang ingin dipindahkan.');
                }
            }

            return redirect()->route('admin.inventory.index')
                             ->with('success', 'Barang berhasil dipindahkan antar ruangan.');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Gagal memindahkan barang: ' . $e->getMessage());
        }
    }

    public function getItems(Lab $lab)
    {
        $summary = $this->inventoryService->getLabInventorySummary($lab->id);
        
        // Filter only items with > 0 total
        $availableItems = collect($summary)->filter(function ($item) {
            return $item['total'] > 0;
        })->values();

        return response()->json($availableItems);
    }

    public function getItemDetails(Lab $lab, Item $item)
    {
        if ($item->tracking_mode->value !== 'AGGREGATE') {
            $units = AssetUnit::with(['batch'])
                ->where('lab_id', $lab->id)
                ->whereHas('batch', function($q) use ($item) {
                    $q->where('item_id', $item->id);
                })
                ->get()
                ->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'asset_tag' => $unit->asset_tag,
                        'university_asset_code' => $unit->university_asset_code,
                        'batch_formatted' => $unit->batch->arrival_formatted ?? '-',
                        'condition_label' => $unit->condition->label(),
                        'condition_color' => $unit->condition->colorClass(),
                    ];
                });
                
            return response()->json(['type' => 'units', 'data' => $units]);
        } else {
            $balances = InventoryBalance::with(['batch'])
                ->where('lab_id', $lab->id)
                ->whereHas('batch', function($q) use ($item) {
                    $q->where('item_id', $item->id);
                })
                ->where('quantity', '>', 0)
                ->get()
                ->map(function ($balance) {
                    return [
                        'id' => $balance->id,
                        'batch_id' => $balance->batch_id,
                        'batch_formatted' => $balance->batch->arrival_formatted ?? '-',
                        'condition_value' => $balance->condition->value,
                        'condition_label' => $balance->condition->label(),
                        'condition_color' => $balance->condition->colorClass(),
                        'max_quantity' => $balance->quantity,
                    ];
                });
                
            return response()->json(['type' => 'balances', 'data' => $balances]);
        }
    }
}
