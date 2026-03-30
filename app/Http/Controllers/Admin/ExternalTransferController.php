<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalTransfer;
use App\Models\Lab;
use App\Models\Item;
use App\Services\InventoryService;
use App\Enums\ConditionEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExternalTransferController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display list of all external transfers
     */
    public function index(Request $request)
    {
        $transfers = ExternalTransfer::with(['item', 'sourceLab', 'targetLab', 'user'])
            ->orderByDesc('transfer_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        $eksternalLab = Lab::where('name', 'Eksternal')->first();

        return view('admin.inventory.external-transfer.index', compact('transfers', 'eksternalLab'));
    }

    /**
     * Show the form for creating a new external transfer from Gudang
     */
    public function create(Request $request)
    {
        $gudangLab = Lab::where('name', 'Gudang')->first();
        
        if (!$gudangLab) {
            return redirect()->route('admin.inventory.index')
                ->with('error', 'Ruangan Gudang tidak ditemukan.');
        }

        // Get items available in Gudang
        $summary = $this->inventoryService->getLabInventorySummary($gudangLab->id);
        $availableItems = collect($summary)->filter(fn($item) => $item['total'] > 0)->values();

        return view('admin.inventory.external-transfer.create', compact('gudangLab', 'availableItems'));
    }

    /**
     * Store a new external transfer
     */
    public function store(Request $request)
    {
        $rules = [
            'item_id' => 'required|exists:items,id',
            'recipient' => 'required|string|max:255',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'tracking_mode' => 'required|string',
        ];

        if ($request->tracking_mode === 'AGGREGATE') {
            $rules['transfers'] = 'required|array|min:1';
            $rules['transfers.*.batch_id'] = 'required|exists:batches,id';
            $rules['transfers.*.condition'] = 'required|string';
            $rules['transfers.*.quantity'] = 'nullable|integer|min:0';
        } else {
            $rules['unit_ids'] = 'required|array|min:1';
            $rules['unit_ids.*'] = 'exists:asset_units,id';
        }

        $request->validate($rules);

        $gudangLab = Lab::where('name', 'Gudang')->firstOrFail();
        $eksternalLab = Lab::where('name', 'Eksternal')->firstOrFail();
        $item = Item::findOrFail($request->item_id);

        try {
            DB::transaction(function () use ($request, $gudangLab, $eksternalLab, $item) {
                $trackingMode = $request->tracking_mode;
                $notes = "Transfer eksternal ke {$request->recipient}: " . ($request->notes ?? '');

                if ($trackingMode !== 'AGGREGATE') {
                    // Transfer individual units
                    $unitIds = $request->unit_ids;
                    $this->inventoryService->transferUnitsToLab($unitIds, $eksternalLab->id, $notes);

                    // Log external transfer
                    ExternalTransfer::create([
                        'item_name' => $item->name,
                        'recipient' => $request->recipient,
                        'transfer_date' => $request->transfer_date,
                        'item_id' => $item->id,
                        'source_lab_id' => $gudangLab->id,
                        'target_lab_id' => $eksternalLab->id,
                        'user_id' => Auth::id(),
                        'tracking_mode' => $trackingMode,
                        'quantity' => count($unitIds),
                        'notes' => $request->notes,
                    ]);
                } else {
                    // Transfer aggregate
                    $totalTransferred = 0;
                    $firstBatchId = null;
                    $firstCondition = null;

                    foreach ($request->transfers as $transfer) {
                        if (empty($transfer['quantity']) || $transfer['quantity'] <= 0) continue;

                        $this->inventoryService->transferAggregateToLab(
                            $gudangLab->id,
                            $eksternalLab->id,
                            $transfer['batch_id'],
                            ConditionEnum::from($transfer['condition']),
                            $transfer['quantity'],
                            $notes
                        );

                        $totalTransferred += $transfer['quantity'];
                        if (!$firstBatchId) {
                            $firstBatchId = $transfer['batch_id'];
                            $firstCondition = $transfer['condition'];
                        }
                    }

                    if ($totalTransferred === 0) {
                        throw new \Exception('Silakan isi jumlah barang yang ingin ditransfer.');
                    }

                    ExternalTransfer::create([
                        'item_name' => $item->name,
                        'recipient' => $request->recipient,
                        'transfer_date' => $request->transfer_date,
                        'item_id' => $item->id,
                        'batch_id' => $firstBatchId,
                        'source_lab_id' => $gudangLab->id,
                        'target_lab_id' => $eksternalLab->id,
                        'user_id' => Auth::id(),
                        'tracking_mode' => $trackingMode,
                        'condition' => $firstCondition,
                        'quantity' => $totalTransferred,
                        'notes' => $request->notes,
                    ]);
                }
            });

            return redirect()->route('admin.external-transfers.index')
                ->with('success', 'Barang berhasil ditransfer ke Eksternal.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mentransfer barang: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of an external transfer (dipinjam <-> dikembalikan)
     */
    public function toggleStatus(ExternalTransfer $externalTransfer)
    {
        if ($externalTransfer->status === 'dipinjam') {
            $externalTransfer->update([
                'status' => 'dikembalikan',
                'returned_date' => now()->toDateString(),
            ]);
            return redirect()->back()->with('success', "Barang \"{$externalTransfer->item_name}\" ditandai sudah dikembalikan.");
        } else {
            $externalTransfer->update([
                'status' => 'dipinjam',
                'returned_date' => null,
            ]);
            return redirect()->back()->with('success', "Status barang \"{$externalTransfer->item_name}\" dikembalikan ke Dipinjam.");
        }
    }

    /**
     * Show detail of a specific external transfer
     */
    public function show(ExternalTransfer $externalTransfer)
    {
        $externalTransfer->load(['item', 'sourceLab', 'targetLab', 'user']);
        return view('admin.inventory.external-transfer.show', compact('externalTransfer'));
    }

    /**
     * Get items available in Gudang (API for AJAX)
     */
    public function getGudangItems()
    {
        $gudangLab = Lab::where('name', 'Gudang')->first();
        if (!$gudangLab) {
            return response()->json([]);
        }

        $summary = $this->inventoryService->getLabInventorySummary($gudangLab->id);
        $availableItems = collect($summary)->filter(fn($item) => $item['total'] > 0)->values();

        return response()->json($availableItems);
    }

    /**
     * Get item details for a specific item in Gudang (API for AJAX)
     */
    public function getItemDetails(Item $item)
    {
        $gudangLab = Lab::where('name', 'Gudang')->first();
        if (!$gudangLab) {
            return response()->json(['error' => 'Gudang not found'], 404);
        }

        if ($item->tracking_mode->value !== 'AGGREGATE') {
            $units = \App\Models\AssetUnit::with(['batch'])
                ->where('lab_id', $gudangLab->id)
                ->whereHas('batch', function ($q) use ($item) {
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
            $balances = \App\Models\InventoryBalance::with(['batch'])
                ->where('lab_id', $gudangLab->id)
                ->whereHas('batch', function ($q) use ($item) {
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
