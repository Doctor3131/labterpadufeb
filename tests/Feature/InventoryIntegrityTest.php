<?php

namespace Tests\Feature;

use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use App\Models\AssetBorrowing;
use App\Models\AssetBorrowingItem;
use App\Models\AssetTypeCode;
use App\Models\AssetUnit;
use App\Models\Batch;
use App\Models\InventoryBalance;
use App\Models\Item;
use App\Models\Lab;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seat_number_receipt_creates_one_available_unit_and_ledger_line_per_seat(): void
    {
        $lab = $this->createLab('Lab Kursi');
        [$item, $batch] = $this->createItemAndBatch(TrackingModeEnum::SEAT_NUMBER, 'Komputer Kursi');

        $units = app(InventoryService::class)->addSeatNumberInventory(
            $lab->id,
            $batch->id,
            ['A1', 'A2', 'B1']
        );

        $this->assertSame(['A1', 'A2', 'B1'], array_column($units, 'asset_tag'));
        $this->assertTrue(collect($units)->every(fn (AssetUnit $unit) => $unit->is_available));
        $this->assertDatabaseCount('asset_units', 3);
        $this->assertDatabaseCount('transaction_lines', 3);
        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'RECEIPT',
            'lab_id' => $lab->id,
        ]);
        $this->assertSame(TrackingModeEnum::SEAT_NUMBER, $item->tracking_mode);
    }

    public function test_structured_receipt_handles_admin_subtype_missing_type_code_and_unusable_condition(): void
    {
        $lab = $this->createLab('EL.302');
        $typeCode = AssetTypeCode::create([
            'code' => 'PC',
            'name' => 'Komputer',
            'default_tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
            'is_borrowable' => false,
        ]);
        [$codedItem, $codedBatch] = $this->createItemAndBatch(
            TrackingModeEnum::STRUCTURED_TAG,
            'Komputer Admin',
            $typeCode
        );

        $adminUnits = app(InventoryService::class)->addStructuredTagInventory(
            $lab->id,
            $codedBatch->id,
            5,
            null,
            ConditionEnum::RUSAK,
            'ADMIN'
        );

        $this->assertCount(1, $adminUnits);
        $this->assertSame('01.0926.PC.EL302.ADMIN', $adminUnits[0]->asset_tag);
        $this->assertFalse($adminUnits[0]->is_available);
        $this->assertSame($codedItem->id, $adminUnits[0]->batch->item_id);

        [, $manualBatch] = $this->createItemAndBatch(TrackingModeEnum::STRUCTURED_TAG, 'Perangkat Manual');
        $manualUnits = app(InventoryService::class)->addStructuredTagInventory(
            $lab->id,
            $manualBatch->id,
            1
        );

        $this->assertNull($manualUnits[0]->asset_tag);
    }

    public function test_empty_condition_update_does_not_create_a_ledger_transaction(): void
    {
        $result = app(InventoryService::class)->updateUnitCondition([], ConditionEnum::RUSAK);

        $this->assertSame([], $result);
        $this->assertDatabaseCount('inventory_transactions', 0);
        $this->assertDatabaseCount('transaction_lines', 0);
    }

    public function test_insufficient_aggregate_condition_transfer_rolls_back_quantities_and_ledger(): void
    {
        $lab = $this->createLab('Lab Agregat');
        [, $batch] = $this->createItemAndBatch(TrackingModeEnum::AGGREGATE, 'Kabel');
        $balance = InventoryBalance::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'condition' => ConditionEnum::BAIK,
            'quantity' => 2,
        ]);

        try {
            app(InventoryService::class)->transferAggregateCondition(
                $lab->id,
                $batch->id,
                ConditionEnum::BAIK,
                ConditionEnum::RUSAK,
                3
            );
            $this->fail('Insufficient aggregate stock must be rejected.');
        } catch (\Exception $exception) {
            $this->assertStringContainsString('Jumlah tidak mencukupi', $exception->getMessage());
        }

        $this->assertSame(2, $balance->fresh()->quantity);
        $this->assertDatabaseMissing('inventory_balances', [
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'condition' => 'RUSAK',
        ]);
        $this->assertDatabaseCount('inventory_transactions', 0);
    }

    public function test_single_aggregate_condition_transfer_preserves_the_specific_university_codes(): void
    {
        $lab = $this->createLab('Lab Kode');
        [, $batch] = $this->createItemAndBatch(TrackingModeEnum::AGGREGATE, 'Adaptor Kode');
        InventoryBalance::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'condition' => ConditionEnum::BAIK,
            'quantity' => 3,
            'university_asset_code_prefix' => 'UA.X1',
        ]);

        [$goodBalance, $damagedBalance] = app(InventoryService::class)->transferAggregateCondition(
            $lab->id,
            $batch->id,
            ConditionEnum::BAIK,
            ConditionEnum::RUSAK,
            1,
            'Adaptor kedua rusak',
            'UA.X2'
        );

        $this->assertSame(2, $goodBalance->quantity);
        $this->assertSame(['UA.X1', 'UA.X3'], $goodBalance->calculated_codes);
        $this->assertSame(1, $damagedBalance->quantity);
        $this->assertSame(['UA.X2'], $damagedBalance->calculated_codes);
    }

    public function test_aggregate_inter_lab_transfer_preserves_total_stock_and_both_ledger_sides(): void
    {
        $source = $this->createLab('Lab Asal');
        $target = $this->createLab('Gudang');
        [, $batch] = $this->createItemAndBatch(TrackingModeEnum::AGGREGATE, 'Adaptor');
        $sourceBalance = InventoryBalance::create([
            'batch_id' => $batch->id,
            'lab_id' => $source->id,
            'condition' => ConditionEnum::BAIK,
            'quantity' => 7,
        ]);

        [$remaining, $received] = app(InventoryService::class)->transferAggregateToLab(
            $source->id,
            $target->id,
            $batch->id,
            ConditionEnum::BAIK,
            3
        );

        $this->assertSame(4, $remaining->quantity);
        $this->assertSame(3, $received->quantity);
        $this->assertSame(7, InventoryBalance::where('batch_id', $batch->id)->sum('quantity'));
        $this->assertDatabaseHas('inventory_transactions', ['type' => 'TRANSFER', 'lab_id' => $source->id]);
        $this->assertDatabaseHas('inventory_transactions', ['type' => 'RECEIPT', 'lab_id' => $target->id]);
        $this->assertDatabaseCount('transaction_lines', 2);
        $this->assertSame(4, $sourceBalance->fresh()->quantity);
    }

    public function test_inventory_summary_combines_tracking_modes_and_excludes_zero_stock(): void
    {
        $lab = $this->createLab('Lab Ringkasan');
        [$unitItem, $unitBatch] = $this->createItemAndBatch(TrackingModeEnum::SEAT_NUMBER, 'Komputer');
        AssetUnit::create([
            'batch_id' => $unitBatch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);
        [, $aggregateBatch] = $this->createItemAndBatch(TrackingModeEnum::AGGREGATE, 'Kabel');
        InventoryBalance::create([
            'batch_id' => $aggregateBatch->id,
            'lab_id' => $lab->id,
            'condition' => ConditionEnum::RUSAK,
            'quantity' => 2,
        ]);
        [, $zeroBatch] = $this->createItemAndBatch(TrackingModeEnum::AGGREGATE, 'Barang Kosong');
        InventoryBalance::create([
            'batch_id' => $zeroBatch->id,
            'lab_id' => $lab->id,
            'condition' => ConditionEnum::BAIK,
            'quantity' => 0,
        ]);

        $summary = app(InventoryService::class)->getLabInventorySummary($lab->id);

        $this->assertSame(['Kabel', 'Komputer'], array_column($summary, 'name'));
        $this->assertSame(2, $summary[0]['conditions']['RUSAK']);
        $this->assertSame(1, $summary[1]['conditions']['BAIK']);
        $this->assertSame($unitItem->id, $summary[1]['id']);
    }

    public function test_aggregate_inventory_http_request_requires_a_quantity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = $this->createLab('Lab Validasi');
        [$item] = $this->createItemAndBatch(TrackingModeEnum::AGGREGATE, 'Kabel Validasi');

        $this->actingAs($admin)
            ->from(route('admin.labs.inventory.create', $lab))
            ->post(route('admin.labs.inventory.store', $lab), [
                'tracking_mode' => TrackingModeEnum::AGGREGATE->value,
                'item_id' => $item->id,
                'batch_id' => 'new',
                'proc_source_code' => '01',
                'arrival_month' => '09',
                'arrival_year' => '2026',
                'condition' => ConditionEnum::BAIK->value,
            ])
            ->assertRedirect(route('admin.labs.inventory.create', $lab))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('inventory_balances', 0);
    }

    public function test_asset_tag_update_rejects_a_tag_used_by_another_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = $this->createLab('Lab Tag');
        [, $batch] = $this->createItemAndBatch(TrackingModeEnum::STRUCTURED_TAG, 'Laptop Tag');
        $first = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => 'TAG-001',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);
        $second = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => 'TAG-002',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.inventory.units.update-asset-tag', $second), [
                'asset_tag' => $first->asset_tag,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('asset_tag');

        $this->assertSame('TAG-002', $second->fresh()->asset_tag);
    }

    public function test_admin_cannot_delete_a_unit_assigned_to_an_active_borrowing(): void
    {
        [$admin, $lab, $item, $unit] = $this->createActivelyBorrowedUnit();

        $this->actingAs($admin)
            ->from(route('admin.labs.inventory.units', [$lab, $item]))
            ->delete(route('admin.inventory.units.destroy', $unit))
            ->assertRedirect(route('admin.labs.inventory.units', [$lab, $item]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('asset_units', ['id' => $unit->id]);
        $this->assertDatabaseHas('asset_borrowing_items', ['asset_unit_id' => $unit->id]);
    }

    public function test_bulk_delete_preserves_units_assigned_to_active_borrowings(): void
    {
        [$admin, $lab, $item, $unit] = $this->createActivelyBorrowedUnit();

        $this->actingAs($admin)
            ->from(route('admin.labs.inventory.units', [$lab, $item]))
            ->post(route('admin.inventory.bulk-delete'), ['unit_ids' => [$unit->id]])
            ->assertRedirect(route('admin.labs.inventory.units', [$lab, $item]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('asset_units', ['id' => $unit->id]);
        $this->assertDatabaseHas('asset_borrowing_items', ['asset_unit_id' => $unit->id]);
    }

    /** @return array{User, Lab, Item, AssetUnit} */
    private function createActivelyBorrowedUnit(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = $this->createLab('Lab Pinjam');
        [$item, $batch] = $this->createItemAndBatch(TrackingModeEnum::STRUCTURED_TAG, 'Laptop');
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.0926.LP.LAB.001',
            'condition' => ConditionEnum::BAIK,
            'is_available' => false,
        ]);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Aktif',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-14',
            'tracking_token' => 'activeunit',
        ]);
        $borrowing->status = 'borrowed';
        $borrowing->save();
        AssetBorrowingItem::create([
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'asset_unit_id' => $unit->id,
            'quantity' => 1,
        ]);

        return [$admin, $lab, $item, $unit];
    }

    private function createLab(string $name): Lab
    {
        return Lab::create(['name' => $name, 'capacity' => 40, 'status' => 'available']);
    }

    /** @return array{Item, Batch} */
    private function createItemAndBatch(
        TrackingModeEnum $mode,
        string $name,
        ?AssetTypeCode $typeCode = null
    ): array {
        $item = Item::create([
            'name' => $name,
            'asset_type_code_id' => $typeCode?->id,
            'tracking_mode' => $mode,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);

        return [$item, $batch];
    }
}
