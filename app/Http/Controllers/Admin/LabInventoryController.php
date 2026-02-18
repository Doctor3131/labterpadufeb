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
     * Display global inventory overview (all labs)
     */
    public function globalIndex(Request $request)
    {
        $labs = Lab::orderBy('name')->get();
        $selectedLabId = $request->query('lab_id');
        
        // Get summary for each lab
        $labSummaries = [];
        $globalTotals = [
            'BAIK' => 0,
            'RUSAK' => 0,
            'HILANG' => 0,
            'MAINTENANCE' => 0,
            'total_items' => 0,
        ];
        
        foreach ($labs as $lab) {
            $summary = $this->inventoryService->getLabInventorySummary($lab->id);
            $totals = [
                'BAIK' => collect($summary)->sum(fn($s) => $s['conditions']['BAIK']),
                'RUSAK' => collect($summary)->sum(fn($s) => $s['conditions']['RUSAK']),
                'HILANG' => collect($summary)->sum(fn($s) => $s['conditions']['HILANG']),
                'MAINTENANCE' => collect($summary)->sum(fn($s) => $s['conditions']['MAINTENANCE']),
                'total_items' => count($summary),
            ];
            
            $labSummaries[] = [
                'lab' => $lab,
                'totals' => $totals,
                'total_units' => array_sum([$totals['BAIK'], $totals['RUSAK'], $totals['HILANG'], $totals['MAINTENANCE']]),
            ];
            
            // Add to global totals
            $globalTotals['BAIK'] += $totals['BAIK'];
            $globalTotals['RUSAK'] += $totals['RUSAK'];
            $globalTotals['HILANG'] += $totals['HILANG'];
            $globalTotals['MAINTENANCE'] += $totals['MAINTENANCE'];
            $globalTotals['total_items'] += $totals['total_items'];
        }
        
        // Get items grouped by Category with optional lab filter
        $itemsQuery = Item::with(['batches.assetUnits', 'batches.inventoryBalances', 'assetTypeCode'])
            ->orderBy('name');
        
        // Apply lab filter if selected
        if ($selectedLabId) {
            $itemsQuery->where(function($query) use ($selectedLabId) {
                // Filter items that have units or balances in the selected lab
                $query->whereHas('batches.assetUnits', function($q) use ($selectedLabId) {
                    $q->where('lab_id', $selectedLabId);
                })->orWhereHas('batches.inventoryBalances', function($q) use ($selectedLabId) {
                    $q->where('lab_id', $selectedLabId);
                });
            });
        }
        
        $items = $itemsQuery->get();
        
        // If lab filter is active, filter the batches/units/balances to only show those in the selected lab
        if ($selectedLabId) {
            $items = $items->map(function($item) use ($selectedLabId) {
                // Filter batches to only include those with units/balances in selected lab
                $item->setRelation('batches', $item->batches->filter(function($batch) use ($selectedLabId) {
                    $hasUnitsInLab = $batch->assetUnits->where('lab_id', $selectedLabId)->isNotEmpty();
                    $hasBalancesInLab = $batch->inventoryBalances->where('lab_id', $selectedLabId)->isNotEmpty();
                    return $hasUnitsInLab || $hasBalancesInLab;
                })->map(function($batch) use ($selectedLabId) {
                    // Filter units and balances within each batch
                    $batch->setRelation('assetUnits', $batch->assetUnits->where('lab_id', $selectedLabId));
                    $batch->setRelation('inventoryBalances', $batch->inventoryBalances->where('lab_id', $selectedLabId));
                    return $batch;
                }));
                return $item;
            });
        }
            
        $groupedItems = $items->groupBy(function($item) {
            return $item->category ?: 'Lainnya';
        })->sortKeys();

        return view('admin.inventory.global', [
            'labSummaries' => $labSummaries,
            'globalTotals' => $globalTotals,
            'conditions' => ConditionEnum::cases(),
            'groupedItems' => $groupedItems,
            'trackingModes' => TrackingModeEnum::cases(),
            'labs' => $labs,
            'selectedLabId' => $selectedLabId,
        ]);
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
        // Get unique items by name to prevent duplicates in dropdown
        $items = Item::with('assetTypeCode')->get()->unique('name')->sortBy('name');
        $assetTypeCodes = AssetTypeCode::orderBy('name')->get();
        $trackingModes = TrackingModeEnum::cases();
        $conditions = ConditionEnum::cases();
        $customCategories = \App\Models\Category::orderBy('name')->get();

        return view('admin.labs.inventory.create', [
            'lab' => $lab,
            'items' => $items,
            'assetTypeCodes' => $assetTypeCodes,
            'trackingModes' => $trackingModes,
            'conditions' => $conditions,
            'customCategories' => $customCategories,
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

        // Handle Asset Type Code (Find or Create on fly) for Structured Tag
        // Handle Asset Type Code (Find or Create on fly)
        $assetTypeCodeId = null;
        if (!empty($validated['asset_type_code'])) {
            $codeStr = $validated['asset_type_code'];
            
            // Map code to name (hardcoded map to match view)
            $names = [
                'H3' => 'PC AIO',
                'I2' => 'TV',
                'BRK' => 'Bracket',
                'J1' => 'Speaker',
                'O1' => 'Laptop',
                'L1' => 'Printer',
                'P' => 'Samsung Tab',
                'MN' => 'Monitor',
                'KY' => 'Keyboard',
                'MS' => 'Mouse',
                'RT' => 'Router',
                'SW' => 'Switch / Hub',
                'K' => 'Kursi',
                'M' => 'Meja',
                'LAIN' => 'Lainnya',
            ];
            $name = $names[$codeStr] ?? $codeStr;

            $assetTypeCode = AssetTypeCode::firstOrCreate(
                ['code' => $codeStr],
                [
                    'name' => $name,
                    'default_tracking_mode' => $mode,
                    'is_borrowable' => true
                ]
            );
            $assetTypeCodeId = $assetTypeCode->id;
        }

        // Get or create item
        if (!empty($validated['new_item_name'])) {
            // Auto-set category untuk STRUCTURED_TAG berdasarkan asset type code
            $category = null;
            if ($mode === TrackingModeEnum::STRUCTURED_TAG && $assetTypeCodeId) {
                $assetTypeCode = AssetTypeCode::find($assetTypeCodeId);
                // Map asset type code ke kategori
                $categoryMap = [
                    'H3' => 'PC',
                    'I2' => 'TV',
                    'BRK' => 'Bracket TV',
                    'J1' => 'Speaker',
                    'O1' => 'Laptop',
                    'L1' => 'Printer',
                    'P' => 'Tablet',
                ];
                $category = $categoryMap[$assetTypeCode->code] ?? null;
            } else {
                // Untuk non-STRUCTURED_TAG, gunakan kategori dari user
                $category = $validated['category'] ?? null;
                
                // Simpan kategori baru jika belum ada (dan bukan dari hardcoded list)
                if ($category && !in_array($category, \App\Enums\CategoryEnum::values())) {
                    \App\Models\Category::firstOrCreate(['name' => $category]);
                }
            }
            
            $item = Item::firstOrCreate(
                [
                    'name' => $validated['new_item_name'],
                    'tracking_mode' => $mode,
                ],
                [
                    'brand' => $validated['brand'] ?? null,
                    'category' => $category,
                    'asset_type_code_id' => $assetTypeCodeId,
                    'description' => $validated['item_description'] ?? null,
                ]
            );
        } else {
            $item = Item::findOrFail($validated['item_id']);
            // Reset type code if item didn't have one? Or keep existing. 
            // For new inventory flow, usually we use existing item's config.
        }

        // Get or create batch
        if (empty($validated['batch_id']) || $validated['batch_id'] === 'new') {
            $batch = Batch::firstOrCreate(
                [
                    'item_id' => $item->id,
                    'proc_source_code' => $validated['proc_source_code'],
                    'arrival_mmyy' => $validated['arrival_mmyy'],
                ],
                [
                    'source_description' => $validated['source_description'] ?? null,
                    'unit_price' => $validated['unit_price'] ?? null,
                ]
            );
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
                    // Auto-generate numeric seat numbers
                    $startSeat = $validated['start_seat'] ?? null;
                    
                    if (!$startSeat) {
                        // Find max seat number for this lab + item
                        // We check asset tags ending with numbers
                        $item = $batch->item;
                        $existingTags = \App\Models\AssetUnit::where('lab_id', $lab->id)
                            ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
                            ->pluck('asset_tag')
                            ->toArray();
                            
                        $max = 0;
                        foreach ($existingTags as $tag) {
                            $parts = explode('-', $tag);
                            $seat = end($parts);
                            if (is_numeric($seat)) {
                                $max = max($max, (int)$seat);
                            }
                        }
                        $startSeat = $max + 1;
                    }

                    $seatNumbers = [];
                    for ($i = 0; $i < $validated['quantity']; $i++) {
                        $seatNumbers[] = (string)($startSeat + $i);
                    }
                    
                    $units = $this->inventoryService->addSeatNumberInventory(
                        $lab->id,
                        $batch->id,
                        $seatNumbers,
                        $condition
                    );
                    $message = count($units) . " unit berhasil ditambahkan";
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
        // Load asset type code relationship
        $item->load('assetTypeCode');
        
        $units = AssetUnit::where('lab_id', $lab->id)
            ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
            ->with(['batch', 'transactionLines.transaction'])
            ->orderByRaw("CASE WHEN subtype = 'ADMIN' THEN 0 ELSE 1 END")
            // For SEAT_NUMBER mode, sort numerically; otherwise sort as string
            ->when($item->tracking_mode === TrackingModeEnum::SEAT_NUMBER, 
                fn($q) => $q->orderByRaw('CAST(asset_tag AS UNSIGNED)'),
                fn($q) => $q->orderBy('asset_tag')
            )
            ->paginate(50);

        $conditionCounts = AssetUnit::where('lab_id', $lab->id)
            ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
            ->selectRaw('`condition`, COUNT(*) as count')
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
        // Load asset type code relationship
        $item->load('assetTypeCode');
        
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
        // Handle duplicate items: find all batches for items with the same name
        $batches = Batch::whereHas('item', function ($query) use ($item) {
            $query->where('name', $item->name);
        })
        ->with('item') // Eager load if needed
        ->get()
        ->unique(function ($batch) {
            return $batch->proc_source_code . $batch->arrival_mmyy;
        })
        ->map(function ($batch) {
            return [
                'id' => $batch->id,
                'label' => "{$batch->proc_source_code}.{$batch->arrival_mmyy} - {$batch->arrival_formatted}",
                'proc_source_code' => $batch->proc_source_code,
                'arrival_mmyy' => $batch->arrival_mmyy,
            ];
        })
        ->values(); // Reset keys after unique

        return response()->json($batches);
    }

    /**
     * Delete all inventory for an item in a lab
     */
    public function destroyItem(Lab $lab, Item $item)
    {
        try {
            // Delete all asset units for this item in this lab
            $deletedUnits = AssetUnit::where('lab_id', $lab->id)
                ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
                ->delete();

            // Delete all inventory balances for this item in this lab
            $deletedBalances = InventoryBalance::where('lab_id', $lab->id)
                ->whereHas('batch', fn($q) => $q->where('item_id', $item->id))
                ->delete();

            $totalDeleted = $deletedUnits + $deletedBalances;

            return redirect()
                ->route('admin.labs.inventory', $lab)
                ->with('success', "Berhasil menghapus {$totalDeleted} unit {$item->name} dari {$lab->name}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    /**
     * Delete a single asset unit
     */
    public function destroyUnit(AssetUnit $unit)
    {
        try {
            $lab = $unit->lab;
            $item = $unit->batch->item;
            $assetTag = $unit->asset_tag;

            $unit->delete();

            return redirect()
                ->route('admin.labs.inventory.units', [$lab, $item])
                ->with('success', "Berhasil menghapus unit {$assetTag}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus unit: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete asset units
     */
    public function bulkDestroyUnits(Request $request)
    {
        $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'exists:asset_units,id',
        ]);

        try {
            // Get first unit to determine lab and item for redirect
            $firstUnit = AssetUnit::with('batch.item', 'lab')->find($request->unit_ids[0]);
            
            if (!$firstUnit) {
                return back()->with('error', 'Unit tidak ditemukan.');
            }

            $lab = $firstUnit->lab;
            $item = $firstUnit->batch->item;
            
            $deletedCount = AssetUnit::whereIn('id', $request->unit_ids)->delete();

            return redirect()
                ->route('admin.labs.inventory.units', [$lab, $item])
                ->with('success', "Berhasil menghapus {$deletedCount} unit.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus unit: ' . $e->getMessage());
        }
    }

    /**
     * Display transaction ledger for a lab
     */
    public function ledger(Lab $lab, Request $request)
    {
        $query = \App\Models\InventoryTransaction::where('lab_id', $lab->id)
            ->with(['user', 'lines.assetUnit', 'lines.inventoryBalance.batch.item']);

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.labs.inventory.ledger', [
            'lab' => $lab,
            'transactions' => $transactions,
        ]);
    }
}
