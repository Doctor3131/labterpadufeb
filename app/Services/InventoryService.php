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
            $typeCode = $batch->item->assetTypeCode?->code ?? '';
            
            // Normalize lab code (remove dots/dashes)
            $labCode = $this->normalizeLabCode($lab->name);
            
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
            
            $labCode = $this->normalizeLabCode($lab->name);
            
            $createdUnits = [];
            
            foreach ($seatNumbers as $seat) {
                // Format: hanya nomor seat saja
                $assetTag = (string)$seat;
                
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
     * @param string|null $universityAssetCodePrefix University asset code prefix for documentation
     * @return InventoryBalance Updated or created balance
     */
    public function addAggregateInventory(
        int $labId,
        int $batchId,
        int $qty,
        ConditionEnum $condition = ConditionEnum::BAIK,
        ?string $universityAssetCodePrefix = null
    ): InventoryBalance {
        return DB::transaction(function () use ($labId, $batchId, $qty, $condition, $universityAssetCodePrefix) {
            // Find or create balance record with lock to prevent race condition
            $balance = InventoryBalance::lockForUpdate()->firstOrCreate(
                [
                    'batch_id' => $batchId,
                    'lab_id' => $labId,
                    'condition' => $condition,
                ],
                ['quantity' => 0]
            );
            
            // Simpan kode universitas jika ada (untuk dokumentasi aggregate)
            if ($universityAssetCodePrefix) {
                $balance->university_asset_code_prefix = $universityAssetCodePrefix;
                $balance->save();
            }
            
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
                    'notes' => $notes ?? $unit->notes,
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
        ?string $notes = null,
        ?string $itemCode = null
    ): array {
        return DB::transaction(function () use ($labId, $batchId, $fromCondition, $toCondition, $qty, $notes, $itemCode) {
            // Get source balance with lock to prevent race condition
            $fromBalance = InventoryBalance::where([
                'batch_id' => $batchId,
                'lab_id' => $labId,
                'condition' => $fromCondition,
            ])->lockForUpdate()->firstOrFail();
            
            if ($fromBalance->quantity < $qty) {
                throw new \Exception("Jumlah tidak mencukupi. Tersedia: {$fromBalance->quantity}, diminta: {$qty}");
            }
            
            // Get or create target balance with lock
            $toBalance = InventoryBalance::lockForUpdate()->firstOrCreate(
                [
                    'batch_id' => $batchId,
                    'lab_id' => $labId,
                    'condition' => $toCondition,
                ],
                ['quantity' => 0]
            );
            
            // Handle university code prefix adjustment for single-item transfer
            if ($itemCode && $qty === 1 && $fromBalance->university_asset_code_prefix) {
                $this->adjustUniversityCodesForSingleTransfer(
                    $fromBalance,
                    $toBalance,
                    $itemCode
                );
            }
            
            // Update quantities
            $fromBalance->decrement('quantity', $qty);
            $toBalance->increment('quantity', $qty);
            
            // Create transaction
            $transaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::CONDITION_CHANGE,
                'lab_id' => $labId,
                'user_id' => Auth::id(),
                'notes' => $notes ?? "Transfer {$qty} unit dari {$fromCondition->label()} ke {$toCondition->label()}" . ($itemCode ? " (kode: {$itemCode})" : ''),
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
     * Adjust university asset code prefixes when transferring a single specific item.
     * 
     * When a specific code (e.g., .X73) is transferred from one condition to another,
     * the source balance's codes need to be reorganized to fill the gap,
     * and the target balance needs to accommodate the new code.
     */
    private function adjustUniversityCodesForSingleTransfer(
        InventoryBalance $fromBalance,
        InventoryBalance $toBalance,
        string $itemCode
    ): void {
        // Generate the current list of codes for the source balance
        $fromCodes = $fromBalance->calculated_codes;
        
        // Find the index of the specific item being transferred
        $codeIndex = array_search($itemCode, $fromCodes);
        
        if ($codeIndex === false) {
            // Item code not found in generated list, skip adjustment
            return;
        }
        
        // Remove the transferred code from the source list
        array_splice($fromCodes, $codeIndex, 1);
        
        // Ensure source balance accurately reflects the remaining codes
        $fromBalance->custom_codes = count($fromCodes) > 0 ? array_values($fromCodes) : null;
        
        // If it still falls back to prefix gracefully, we try to preserve prefix
        if (count($fromCodes) > 0) {
            // Setup an appropriate prefix (first code) in case it's used as fallback
            $fromBalance->university_asset_code_prefix = $fromCodes[0];
        } else {
            // If empty, clearing out prefix
             $fromBalance->university_asset_code_prefix = null;
        }
        
        $fromBalance->save();
        
        // Handle the target balance's university code
        $toCodes = $toBalance->calculated_codes;
        $toCodes[] = $itemCode;
        
        // Sort codes conceptually if they are string comparable
        sort($toCodes);
        
        $toBalance->custom_codes = array_values($toCodes);
        
        if (count($toCodes) > 0) {
            $toBalance->university_asset_code_prefix = $toCodes[0];
        }
        
        $toBalance->save();
    }

    /**
     * Generate a list of codes from a prefix and quantity.
     * Mirrors the Blade template logic for code generation.
     */
    private function generateCodesFromPrefix(string $prefix, int $qty): array
    {
        $codes = [];
        
        if (preg_match('/^(.+\.)([A-Za-z]*)(\d+)$/', $prefix, $matches)) {
            $base = $matches[1];
            $letters = $matches[2];
            $startNum = (int)$matches[3];
            
            for ($i = 0; $i < $qty; $i++) {
                $codes[] = $base . $letters . ($startNum + $i);
            }
        } else {
            for ($i = 1; $i <= $qty; $i++) {
                $codes[] = $prefix . '-' . $i;
            }
        }
        
        return $codes;
    }

    /**
     * Transfer asset units to another Lab (e.g. Gudang)
     */
    public function transferUnitsToLab(array $unitIds, int $targetLabId, ?string $notes = null): array
    {
        return DB::transaction(function () use ($unitIds, $targetLabId, $notes) {
            $units = AssetUnit::whereIn('id', $unitIds)->get();
            if ($units->isEmpty()) {
                return [];
            }

            $sourceLabId = $units->first()->lab_id;
            $targetLab = Lab::findOrFail($targetLabId);
            $sourceLab = Lab::findOrFail($sourceLabId);

            // Create Outgoing transaction for Source Lab
            $outTransaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::TRANSFER, // transferred to another lab
                'lab_id' => $sourceLabId,
                'user_id' => Auth::id(),
                'notes' => $notes ?? "Pindah {$units->count()} unit ke {$targetLab->name}",
            ]);

            // Create Incoming transaction for Target Lab
            $inTransaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::RECEIPT,
                'lab_id' => $targetLabId,
                'user_id' => Auth::id(),
                'notes' => $notes ?? "Pindahan {$units->count()} unit dari {$sourceLab->name}",
            ]);

            $updatedUnits = [];

            foreach ($units as $unit) {
                // Out line
                TransactionLine::create([
                    'transaction_id' => $outTransaction->id,
                    'asset_unit_id' => $unit->id,
                    'from_condition' => $unit->condition,
                ]);

                // Update location
                $unit->update(['lab_id' => $targetLabId]);

                // In line
                TransactionLine::create([
                    'transaction_id' => $inTransaction->id,
                    'asset_unit_id' => $unit->id,
                    'to_condition' => $unit->condition,
                ]);

                $updatedUnits[] = $unit;
            }

            return $updatedUnits;
        });
    }

    /**
     * Transfer aggregate inventory to another Lab (e.g. Gudang)
     */
    public function transferAggregateToLab(
        int $sourceLabId,
        int $targetLabId,
        int $batchId,
        ConditionEnum $condition,
        int $qty,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($sourceLabId, $targetLabId, $batchId, $condition, $qty, $notes) {
            $sourceLab = Lab::findOrFail($sourceLabId);
            $targetLab = Lab::findOrFail($targetLabId);

            // Get source balance
            $fromBalance = InventoryBalance::where([
                'batch_id' => $batchId,
                'lab_id' => $sourceLabId,
                'condition' => $condition,
            ])->lockForUpdate()->firstOrFail();

            if ($fromBalance->quantity < $qty) {
                throw new \Exception("Jumlah tidak mencukupi. Tersedia: {$fromBalance->quantity}, diminta: {$qty}");
            }

            // Get target balance
            $toBalance = InventoryBalance::lockForUpdate()->firstOrCreate(
                [
                    'batch_id' => $batchId,
                    'lab_id' => $targetLabId,
                    'condition' => $condition,
                ],
                ['quantity' => 0]
            );

            // Update quantities
            $fromBalance->decrement('quantity', $qty);
            $toBalance->increment('quantity', $qty);

            // Outgoing transaction
            $outTransaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::TRANSFER,
                'lab_id' => $sourceLabId,
                'user_id' => Auth::id(),
                'notes' => $notes ?? "Pindah {$qty} unit ke {$targetLab->name}",
            ]);
            TransactionLine::create([
                'transaction_id' => $outTransaction->id,
                'inventory_balance_id' => $fromBalance->id,
                'from_condition' => $condition,
                'quantity' => $qty,
            ]);

            // Incoming transaction
            $inTransaction = InventoryTransaction::create([
                'type' => TransactionTypeEnum::RECEIPT,
                'lab_id' => $targetLabId,
                'user_id' => Auth::id(),
                'notes' => $notes ?? "Pindahan {$qty} unit dari {$sourceLab->name}",
            ]);
            TransactionLine::create([
                'transaction_id' => $inTransaction->id,
                'inventory_balance_id' => $toBalance->id,
                'to_condition' => $condition,
                'quantity' => $qty,
            ]);

            return [$fromBalance->fresh(), $toBalance->fresh()];
        });
    }

    /**
     * Generate asset tag from components
     * Format: {proc_source}.{arrival_mmyy}.{type_code}.{lab_code}.{seq|ADMIN}
     * Returns empty string if type code is empty (to be filled manually by admin)
     */
    public function generateAssetTag(
        string $procSource,
        string $arrivalMmyy,
        string $typeCode,
        string $labCode,
        ?int $seq = null,
        ?string $subtype = null
    ): ?string {
        // If type code is empty, return null so admin can fill manually later
        if (empty($typeCode)) {
            return null;
        }
        
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
        $labCode = $this->normalizeLabCode($lab->name);
        
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
        
        // Get unit counts by item and condition (for SEAT_NUMBER and STRUCTURED_TAG modes)
        $unitCounts = DB::table('asset_units')
            ->where('asset_units.lab_id', $labId)
            ->join('batches', 'asset_units.batch_id', '=', 'batches.id')
            ->join('items', 'batches.item_id', '=', 'items.id')
            ->select('items.id as item_id', 'items.name as item_name', 'items.category', 'items.tracking_mode', 'asset_units.condition as condition')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('items.id', 'items.name', 'items.category', 'items.tracking_mode', 'asset_units.condition')
            ->get();
        
        // Get aggregate balances (for AGGREGATE mode)
        $balanceCounts = DB::table('inventory_balances')
            ->where('inventory_balances.lab_id', $labId)
            ->join('batches', 'inventory_balances.batch_id', '=', 'batches.id')
            ->join('items', 'batches.item_id', '=', 'items.id')
            ->select('items.id as item_id', 'items.name as item_name', 'items.category', 'items.tracking_mode', 'inventory_balances.condition as condition')
            ->selectRaw('SUM(inventory_balances.quantity) as count')
            ->groupBy('items.id', 'items.name', 'items.category', 'items.tracking_mode', 'inventory_balances.condition')
            ->get();
        
        // Merge and organize by item
        $summary = [];
        
        foreach ($unitCounts->merge($balanceCounts) as $row) {
            $itemId = $row->item_id;
            
            if (!isset($summary[$itemId])) {
                $summary[$itemId] = [
                    'id' => $itemId,
                    'name' => $row->item_name,
                    'category' => $row->category,
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
            
            // Now condition is always a string from DB::table
            $conditionValue = $row->condition;
            
            if (isset($summary[$itemId]['conditions'][$conditionValue])) {
                $summary[$itemId]['conditions'][$conditionValue] += $row->count;
                $summary[$itemId]['total'] += $row->count;
            }
        }
        
        // Remove items that have no stock (total == 0) after all calculations
        $summary = array_filter($summary, function($item) {
            return $item['total'] > 0;
        });
        
        // Sort by item name
        usort($summary, fn($a, $b) => strcmp($a['name'], $b['name']));
        
        // Re-index before returning
        return array_values($summary);
    }
}
