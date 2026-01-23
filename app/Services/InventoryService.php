<?php

namespace App\Services;

use App\Enums\ConditionEnum;
use App\Enums\TransactionTypeEnum;
use App\Enums\TrackingModeEnum;
use App\Models\AssetUnit;
use App\Models\Batch;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Lab;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    /**
     * Add inventory with STRUCTURED_TAG mode
     * Generates asset tags in format: {proc_source}.{arrival_mmyy}.{type_code}.{lab_code}.{seq|ADMIN}
     * 
     * @param int $labId
     * @param int $batchId
     * @param int $qty Number of units to create
     * @param int|null $startSeq Starting sequence number (auto-detect if null)
     * @param ConditionEnum $condition Initial condition
     * @param string|null $subtype Optional subtype (e.g., 'ADMIN')
     * @return array Created asset units
     */
    public function addStructuredTagInventory(
        int $labId,
        int $batchId,
        int $qty,
        ?int $startSeq = null,
        ConditionEnum $condition = ConditionEnum::BAIK,
        ?string $subtype = null
    ): array {
        return DB::transaction(function () use ($labId, $batchId, $qty, $startSeq, $condition, $subtype) {
            $batch = Batch::with('item.assetTypeCode')->findOrFail($batchId);
            $lab = Lab::findOrFail($labId);
            
            // Get type code from item's asset type code
            $typeCode = $batch->item->assetTypeCode?->code ?? 'XX';
            
            // Normalize lab code (remove dots/dashes)
            $labCode = $this->normalizeLabCode($lab->code);
            
            $createdUnits = [];
            
            // For ADMIN subtype, we don't use sequence numbers
            if ($subtype === 'ADMIN') {
                $assetTag = $this->generateAssetTag(
                    $batch->proc_source_code,
                    $batch->arrival_mmyy,
                    $typeCode,
                    $labCode,
                    null,
                    'ADMIN'
                );
                
                $unit = AssetUnit::create([
                    'batch_id' => $batchId,
                    'lab_id' => $labId,
                    'proc_source_code' => $batch->proc_source_code,
                    'arrival_mmyy' => $batch->arrival_mmyy,
                    'type_code' => $typeCode,
                    'lab_code_snapshot' => $labCode,
                    'seq_number' => null,
                    'asset_tag' => $assetTag,
                    'subtype' => 'ADMIN',
                    'condition' => $condition,
                    'is_available' => $condition->isUsable(),
                ]);
                
                $createdUnits[] = $unit;
            } else {
                // Get next sequence number
                $currentSeq = $startSeq ?? $this->getNextSequence(
                    $batch->proc_source_code,
                    $batch->arrival_mmyy,
                    $typeCode,
                    $labId
                );
                
                for ($i = 0; $i < $qty; $i++) {
                    $assetTag = $this->generateAssetTag(
                        $batch->proc_source_code,
                        $batch->arrival_mmyy,
                        $typeCode,
                        $labCode,
                        $currentSeq
                    );
                    
                    $unit = AssetUnit::create([
                        'batch_id' => $batchId,
                        'lab_id' => $labId,
                        'proc_source_code' => $batch->proc_source_code,
                        'arrival_mmyy' => $batch->arrival_mmyy,
                        'type_code' => $typeCode,
                        'lab_code_snapshot' => $labCode,
                        'seq_number' => $currentSeq,
                        'asset_tag' => $assetTag,
                        'subtype' => $subtype,
                        'condition' => $condition,
                        'is_available' => $condition->isUsable(),
                    ]);
                    
                    $createdUnits[] = $unit;
                    $currentSeq++;
                }
            }
            
            // Create receipt transaction
            $this->createReceiptTransaction($labId, $createdUnits, $condition);
            
            return $createdUnits;
        });
    }

    /**
     * Add inventory with SEAT_NUMBER mode
     * 
     * @param int $labId
     * @param int $batchId
     * @param array $seatNumbers List of seat identifiers (e.g., ['01', '02', '03'] or ['A1', 'A2'])
     * @param ConditionEnum $condition Initial condition
     * @return array Created asset units
     */
    public function addSeatNumberInventory(
        int $labId,
        int $batchId,
        array $seatNumbers,
        ConditionEnum $condition = ConditionEnum::BAIK
    ): array {
        return DB::transaction(function () use ($labId, $batchId, $seatNumbers, $condition) {
            $batch = Batch::with('item')->findOrFail($batchId);
            $lab = Lab::findOrFail($labId);
            
            $labCode = $this->normalizeLabCode($lab->code);
            $itemPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $batch->item->name), 0, 3));
            
            $createdUnits = [];
            
            foreach ($seatNumbers as $seat) {
                // Format: {labCode}-{itemPrefix}-{seat}
                $assetTag = "{$labCode}-{$itemPrefix}-{$seat}";
                
                $unit = AssetUnit::create([
                    'batch_id' => $batchId,
                    'lab_id' => $labId,
                    'lab_code_snapshot' => $labCode,
                    'asset_tag' => $assetTag,
                    'condition' => $condition,
                    'is_available' => $condition->isUsable(),
                ]);
                
                $createdUnits[] = $unit;
            }
            
            // Create receipt transaction
            $this->createReceiptTransaction($labId, $createdUnits, $condition);
            
            return $createdUnits;
        });
    }

    /**
     * Add inventory with AGGREGATE mode
     * 
     * @param int $labId
     * @param int $batchId
     * @param int $qty Quantity to add
     * @param ConditionEnum $condition Condition of the items
     * @return InventoryBalance Updated or created balance
     */
    public function addAggregateInventory(
        int $labId,
        int $batchId,
        int $qty,
        ConditionEnum $condition = ConditionEnum::BAIK
    ): InventoryBalance {
        return DB::transaction(function () use ($labId, $batchId, $qty, $condition) {
            // Find or create balance record
            $balance = InventoryBalance::firstOrCreate(
                [
                    'batch_id' => $batchId,
                    'lab_id' => $labId,
                    'condition' => $condition,
                ],
                ['quantity' => 0]
            );
            
            $balance->increment('quantity', $qty);
            
            // Create receipt transaction
            $transaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::RECEIPT,
                'lab_id' => $labId,
                'user_id' => Auth::id(),
                'notes' => "Penerimaan {$qty} unit (agregat)",
            ]);
            
            TransactionLine::create([
                'transaction_id' => $transaction->id,
                'inventory_balance_id' => $balance->id,
                'to_condition' => $condition,
                'quantity' => $qty,
            ]);
            
            return $balance->fresh();
        });
    }

    /**
     * Update condition of multiple asset units
     * 
     * @param array $unitIds Array of asset unit IDs
     * @param ConditionEnum $newCondition New condition
     * @param string|null $notes Optional notes
     * @return array Updated units
     */
    public function updateUnitCondition(
        array $unitIds,
        ConditionEnum $newCondition,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($unitIds, $newCondition, $notes) {
            $units = AssetUnit::whereIn('id', $unitIds)->get();
            
            if ($units->isEmpty()) {
                return [];
            }
            
            // Group by lab for transaction header
            $labId = $units->first()->lab_id;
            
            $transaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::CONDITION_CHANGE,
                'lab_id' => $labId,
                'user_id' => Auth::id(),
                'notes' => $notes ?? "Perubahan kondisi {$units->count()} unit ke {$newCondition->label()}",
            ]);
            
            $updatedUnits = [];
            
            foreach ($units as $unit) {
                $oldCondition = $unit->condition;
                
                // Create transaction line
                TransactionLine::create([
                    'transaction_id' => $transaction->id,
                    'asset_unit_id' => $unit->id,
                    'from_condition' => $oldCondition,
                    'to_condition' => $newCondition,
                ]);
                
                // Update unit
                $unit->update([
                    'condition' => $newCondition,
                    'is_available' => $newCondition->isUsable(),
                ]);
                
                $updatedUnits[] = $unit;
            }
            
            return $updatedUnits;
        });
    }

    /**
     * Transfer quantity between conditions for aggregate inventory
     * 
     * @param int $labId Lab ID
     * @param int $batchId Batch ID
     * @param ConditionEnum $fromCondition Source condition
     * @param ConditionEnum $toCondition Target condition
     * @param int $qty Quantity to transfer
     * @param string|null $notes Optional notes
     * @return array [fromBalance, toBalance]
     */
    public function transferAggregateCondition(
        int $labId,
        int $batchId,
        ConditionEnum $fromCondition,
        ConditionEnum $toCondition,
        int $qty,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($labId, $batchId, $fromCondition, $toCondition, $qty, $notes) {
            // Get source balance
            $fromBalance = InventoryBalance::where([
                'batch_id' => $batchId,
                'lab_id' => $labId,
                'condition' => $fromCondition,
            ])->firstOrFail();
            
            if ($fromBalance->quantity < $qty) {
                throw new \Exception("Jumlah tidak mencukupi. Tersedia: {$fromBalance->quantity}, diminta: {$qty}");
            }
            
            // Get or create target balance
            $toBalance = InventoryBalance::firstOrCreate(
                [
                    'batch_id' => $batchId,
                    'lab_id' => $labId,
                    'condition' => $toCondition,
                ],
                ['quantity' => 0]
            );
            
            // Update quantities
            $fromBalance->decrement('quantity', $qty);
            $toBalance->increment('quantity', $qty);
            
            // Create transaction
            $transaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::CONDITION_CHANGE,
                'lab_id' => $labId,
                'user_id' => Auth::id(),
                'notes' => $notes ?? "Transfer {$qty} unit dari {$fromCondition->label()} ke {$toCondition->label()}",
            ]);
            
            TransactionLine::create([
                'transaction_id' => $transaction->id,
                'inventory_balance_id' => $fromBalance->id,
                'from_condition' => $fromCondition,
                'to_condition' => $toCondition,
                'quantity' => $qty,
            ]);
            
            return [$fromBalance->fresh(), $toBalance->fresh()];
        });
    }

    /**
     * Generate asset tag from components
     * Format: {proc_source}.{arrival_mmyy}.{type_code}.{lab_code}.{seq|ADMIN}
     */
    public function generateAssetTag(
        string $procSource,
        string $arrivalMmyy,
        string $typeCode,
        string $labCode,
        ?int $seq = null,
        ?string $subtype = null
    ): string {
        $suffix = $subtype === 'ADMIN' ? 'ADMIN' : str_pad($seq, 3, '0', STR_PAD_LEFT);
        
        return "{$procSource}.{$arrivalMmyy}.{$typeCode}.{$labCode}.{$suffix}";
    }

    /**
     * Get next available sequence number for a given combination
     */
    public function getNextSequence(
        string $procSource,
        string $arrivalMmyy,
        string $typeCode,
        int $labId
    ): int {
        $lab = Lab::findOrFail($labId);
        $labCode = $this->normalizeLabCode($lab->code);
        
        $maxSeq = AssetUnit::where('proc_source_code', $procSource)
            ->where('arrival_mmyy', $arrivalMmyy)
            ->where('type_code', $typeCode)
            ->where('lab_code_snapshot', $labCode)
            ->whereNotNull('seq_number')
            ->max('seq_number');
        
        return ($maxSeq ?? 0) + 1;
    }

    /**
     * Normalize lab code by removing dots and dashes
     * e.g., "EL.301" becomes "EL301"
     */
    public function normalizeLabCode(string $code): string
    {
        return str_replace(['.', '-'], '', $code);
    }

    /**
     * Create receipt transaction for new units
     */
    private function createReceiptTransaction(int $labId, array $units, ConditionEnum $condition): InventoryTransaction
    {
        $transaction = InventoryTransaction::create([
            'type' => TransactionTypeEnum::RECEIPT,
            'lab_id' => $labId,
            'user_id' => Auth::id(),
            'notes' => "Penerimaan " . count($units) . " unit baru",
        ]);
        
        foreach ($units as $unit) {
            TransactionLine::create([
                'transaction_id' => $transaction->id,
                'asset_unit_id' => $unit->id,
                'to_condition' => $condition,
            ]);
        }
        
        return $transaction;
    }

    /**
     * Get inventory summary for a lab
     */
    public function getLabInventorySummary(int $labId): array
    {
        $lab = Lab::findOrFail($labId);
        
        // Get unit counts by item and condition
        $unitCounts = AssetUnit::where('lab_id', $labId)
            ->join('batches', 'asset_units.batch_id', '=', 'batches.id')
            ->join('items', 'batches.item_id', '=', 'items.id')
            ->selectRaw('items.id as item_id, items.name as item_name, items.tracking_mode, asset_units.condition, COUNT(*) as count')
            ->groupBy('items.id', 'items.name', 'items.tracking_mode', 'asset_units.condition')
            ->get();
        
        // Get aggregate balances
        $balanceCounts = InventoryBalance::where('lab_id', $labId)
            ->join('batches', 'inventory_balances.batch_id', '=', 'batches.id')
            ->join('items', 'batches.item_id', '=', 'items.id')
            ->selectRaw('items.id as item_id, items.name as item_name, items.tracking_mode, inventory_balances.condition, SUM(quantity) as count')
            ->groupBy('items.id', 'items.name', 'items.tracking_mode', 'inventory_balances.condition')
            ->get();
        
        // Merge and organize by item
        $summary = [];
        
        foreach ($unitCounts->merge($balanceCounts) as $row) {
            $itemId = $row->item_id;
            
            if (!isset($summary[$itemId])) {
                $summary[$itemId] = [
                    'id' => $itemId,
                    'name' => $row->item_name,
                    'tracking_mode' => $row->tracking_mode,
                    'conditions' => [
                        'BAIK' => 0,
                        'RUSAK' => 0,
                        'HILANG' => 0,
                        'MAINTENANCE' => 0,
                    ],
                    'total' => 0,
                ];
            }
            
            $conditionValue = $row->condition instanceof ConditionEnum 
                ? $row->condition->value 
                : $row->condition;
            
            $summary[$itemId]['conditions'][$conditionValue] += $row->count;
            $summary[$itemId]['total'] += $row->count;
        }
        
        return array_values($summary);
    }
}
