<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryRequest;
use App\Http\Requests\Inventory\TransferBalanceRequest;
use App\Http\Requests\Inventory\UpdateConditionRequest;
use App\Models\AssetTypeCode;
use App\Models\AssetUnit;
use App\Models\Batch;
use App\Models\InventoryBalance;
use App\Models\Item;
use App\Models\Lab;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class LabInventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory summary for a lab
     */
    public function index(Lab $lab)
    {
        $summary = $this->inventoryService->getLabInventorySummary($lab->id);
        
        return view('admin.labs.inventory.index', [
            'lab' => $lab,
            'summary' => $summary,
            'conditions' => ConditionEnum::cases(),
        ]);
    }

    /**
     * Show form to add inventory
     */
    public function create(Lab $lab)
    {
        $items = Item::with('assetTypeCode')->orderBy('name')->get();
        $assetTypeCodes = AssetTypeCode::orderBy('name')->get();
        $trackingModes = TrackingModeEnum::cases();
        $conditions = ConditionEnum::cases();

        return view('admin.labs.inventory.create', [
            'lab' => $lab,
            'items' => $items,
            'assetTypeCodes' => $assetTypeCodes,
            'trackingModes' => $trackingModes,
            'conditions' => $conditions,
        ]);
    }

    /**
     * Store new inventory
     */
    public function store(StoreInventoryRequest $request, Lab $lab)
    {
        $validated = $request->validated();
        $mode = TrackingModeEnum::from($validated['tracking_mode']);
        $condition = ConditionEnum::from($validated['condition']);

        // Get or create item
        if (!empty($validated['new_item_name'])) {
            $item = Item::create([
                'name' => $validated['new_item_name'],
                'asset_type_code_id' => $validated['asset_type_code_id'] ?? null,
                'tracking_mode' => $mode,
            ]);
        } else {
            $item = Item::findOrFail($validated['item_id']);
        }

        // Get or create batch
        if (empty($validated['batch_id']) || $validated['batch_id'] === 'new') {
            $batch = Batch::create([
                'item_id' => $item->id,
                'proc_source_code' => $validated['proc_source_code'],
                'arrival_mmyy' => $validated['arrival_mmyy'],
                'source_description' => $validated['source_description'] ?? null,
                'unit_price' => $validated['unit_price'] ?? null,
            ]);
        } else {
            $batch = Batch::findOrFail($validated['batch_id']);
        }

        // Process based on mode
        try {
            switch ($mode) {
                case TrackingModeEnum::STRUCTURED_TAG:
                    $units = $this->inventoryService->addStructuredTagInventory(
                        $lab->id,
                        $batch->id,
                        $validated['quantity'],
                        $validated['start_seq'] ?? null,
                        $condition,
                        $validated['subtype'] ?? null
                    );
                    $message = count($units) . " unit berhasil ditambahkan dengan asset tag.";
                    break;

                case TrackingModeEnum::SEAT_NUMBER:
                    // Parse seat numbers (comma or space separated)
                    $seatNumbers = preg_split('/[\s,]+/', $validated['seat_numbers']);
                    $seatNumbers = array_filter(array_map('trim', $seatNumbers));
                    
                    $units = $this->inventoryService->addSeatNumberInventory(
                        $lab->id,
                        $batch->id,
                        $seatNumbers,
                        $condition
                    );
                    $message = count($units) . " unit berhasil ditambahkan dengan seat number.";
                    break;

                case TrackingModeEnum::AGGREGATE:
                    $this->inventoryService->addAggregateInventory(
                        $lab->id,
                        $batch->id,
                        $validated['quantity'],
                        $condition
                    );
                    $message = $validated['quantity'] . " unit berhasil ditambahkan (agregat).";
                    break;
            }

            return redirect()
                ->route('admin.labs.inventory', $lab)
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan inventory: ' . $e->getMessage());
        }
    }

    /**
     * Show asset units for an item in a lab
     */
    public function showUnits(Lab $lab, Item $item)
    {
        $units = AssetUnit::where('lab_id', $lab->id)
            ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
            ->with('batch')
            ->orderBy('asset_tag')
            ->paginate(50);

        $conditionCounts = AssetUnit::where('lab_id', $lab->id)
            ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
            ->selectRaw('condition, COUNT(*) as count')
            ->groupBy('condition')
            ->pluck('count', 'condition');

        return view('admin.labs.inventory.units', [
            'lab' => $lab,
            'item' => $item,
            'units' => $units,
            'conditionCounts' => $conditionCounts,
            'conditions' => ConditionEnum::cases(),
        ]);
    }

    /**
     * Show aggregate balances for an item in a lab
     */
    public function showBalances(Lab $lab, Item $item)
    {
        $balances = InventoryBalance::where('lab_id', $lab->id)
            ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
            ->with('batch')
            ->get()
            ->groupBy('batch_id');

        return view('admin.labs.inventory.balances', [
            'lab' => $lab,
            'item' => $item,
            'balances' => $balances,
            'conditions' => ConditionEnum::cases(),
        ]);
    }

    /**
     * Bulk update condition of units
     */
    public function bulkUpdateCondition(UpdateConditionRequest $request)
    {
        $validated = $request->validated();
        $condition = ConditionEnum::from($validated['condition']);

        try {
            $units = $this->inventoryService->updateUnitCondition(
                $validated['unit_ids'],
                $condition,
                $validated['notes'] ?? null
            );

            // Get lab from first unit for redirect
            $lab = AssetUnit::find($validated['unit_ids'][0])?->lab;

            return redirect()
                ->back()
                ->with('success', count($units) . " unit berhasil diupdate ke kondisi {$condition->label()}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupdate kondisi: ' . $e->getMessage());
        }
    }

    /**
     * Transfer aggregate balance between conditions
     */
    public function transferBalance(TransferBalanceRequest $request, Lab $lab)
    {
        $validated = $request->validated();
        $fromCondition = ConditionEnum::from($validated['from_condition']);
        $toCondition = ConditionEnum::from($validated['to_condition']);

        try {
            $this->inventoryService->transferAggregateCondition(
                $lab->id,
                $validated['batch_id'],
                $fromCondition,
                $toCondition,
                $validated['quantity'],
                $validated['notes'] ?? null
            );

            return redirect()
                ->back()
                ->with('success', "Berhasil transfer {$validated['quantity']} unit dari {$fromCondition->label()} ke {$toCondition->label()}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal transfer: ' . $e->getMessage());
        }
    }

    /**
     * Get batches for an item (AJAX)
     */
    public function getBatches(Item $item)
    {
        $batches = $item->batches()->get()->map(function ($batch) {
            return [
                'id' => $batch->id,
                'label' => "{$batch->proc_source_code}.{$batch->arrival_mmyy} - {$batch->arrival_formatted}",
                'proc_source_code' => $batch->proc_source_code,
                'arrival_mmyy' => $batch->arrival_mmyy,
            ];
        });

        return response()->json($batches);
    }
}
