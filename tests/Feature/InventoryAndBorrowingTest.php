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

class InventoryAndBorrowingTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregate_receipt_updates_balance_and_writes_a_ledger_entry(): void
    {
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Router',
            'tracking_mode' => TrackingModeEnum::AGGREGATE,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);

        $balance = app(InventoryService::class)->addAggregateInventory(
            $lab->id,
            $batch->id,
            5,
            ConditionEnum::BAIK,
            'UA-ROUTER'
        );

        $this->assertSame(5, $balance->quantity);
        $this->assertSame('UA-ROUTER', $balance->university_asset_code_prefix);
        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'RECEIPT',
            'lab_id' => $lab->id,
        ]);
        $this->assertDatabaseHas('transaction_lines', [
            'inventory_balance_id' => $balance->id,
            'to_condition' => 'BAIK',
            'quantity' => 5,
        ]);

        [$goodBalance, $damagedBalance] = app(InventoryService::class)->transferAggregateCondition(
            $lab->id,
            $batch->id,
            ConditionEnum::BAIK,
            ConditionEnum::RUSAK,
            2
        );

        $this->assertSame(3, $goodBalance->quantity);
        $this->assertSame(2, $damagedBalance->quantity);
        $this->assertDatabaseHas('transaction_lines', [
            'inventory_balance_id' => $goodBalance->id,
            'from_condition' => 'BAIK',
            'to_condition' => 'RUSAK',
            'quantity' => 2,
        ]);
    }

    public function test_condition_change_updates_unit_availability_and_ledger(): void
    {
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Uji',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.0926.L1.LABUJI.002',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);

        app(InventoryService::class)->updateUnitCondition(
            [$unit->id],
            ConditionEnum::RUSAK,
            'Layar rusak'
        );

        $this->assertDatabaseHas('asset_units', [
            'id' => $unit->id,
            'condition' => 'RUSAK',
            'is_available' => false,
            'notes' => 'Layar rusak',
        ]);
        $this->assertDatabaseHas('transaction_lines', [
            'asset_unit_id' => $unit->id,
            'from_condition' => 'BAIK',
            'to_condition' => 'RUSAK',
        ]);
    }

    public function test_structured_receipt_generates_sequential_tags_and_ledger_lines(): void
    {
        $lab = Lab::create(['name' => 'EL.301', 'capacity' => 40, 'status' => 'available']);
        $typeCode = AssetTypeCode::create([
            'code' => 'L1',
            'name' => 'Laptop',
            'default_tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
            'is_borrowable' => true,
        ]);
        $item = Item::create([
            'name' => 'Laptop Uji',
            'asset_type_code_id' => $typeCode->id,
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);

        $units = app(InventoryService::class)->addStructuredTagInventory(
            $lab->id,
            $batch->id,
            2,
            7
        );

        $this->assertSame(['01.0926.L1.EL301.007', '01.0926.L1.EL301.008'], array_column($units, 'asset_tag'));
        $this->assertTrue($units[0]->is_available);
        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'RECEIPT',
            'lab_id' => $lab->id,
        ]);
        $this->assertDatabaseCount('transaction_lines', 2);
    }

    public function test_unit_transfer_moves_assets_and_records_both_sides_of_the_ledger(): void
    {
        $sourceLab = Lab::create(['name' => 'Lab Asal', 'capacity' => 40, 'status' => 'available']);
        $targetLab = Lab::create(['name' => 'Gudang', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Uji',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $sourceLab->id,
            'asset_tag' => '01.0926.L1.ASAL.001',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);

        app(InventoryService::class)->transferUnitsToLab([$unit->id], $targetLab->id);

        $this->assertDatabaseHas('asset_units', [
            'id' => $unit->id,
            'lab_id' => $targetLab->id,
        ]);
        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'TRANSFER',
            'lab_id' => $sourceLab->id,
        ]);
        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'RECEIPT',
            'lab_id' => $targetLab->id,
        ]);
        $this->assertDatabaseCount('transaction_lines', 2);
    }

    public function test_admin_approval_and_handout_marks_the_assigned_asset_unavailable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Uji',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.0926.L1.LABUJI.001',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Uji',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'borrower_address' => 'Alamat Uji',
            'lab_id' => $lab->id,
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-13',
            'tracking_token' => 'borrow1234',
        ]);
        $borrowingItem = AssetBorrowingItem::create([
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.approve', $borrowing))
            ->assertRedirect();

        $this->assertDatabaseHas('asset_borrowings', [
            'id' => $borrowing->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.handout', $borrowing), [
                'unit_assignments' => [$borrowingItem->id => [$unit->id]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('asset_borrowings', [
            'id' => $borrowing->id,
            'status' => 'borrowed',
            'handed_out_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('asset_borrowing_items', [
            'id' => $borrowingItem->id,
            'asset_unit_id' => $unit->id,
        ]);
        $this->assertDatabaseHas('asset_units', [
            'id' => $unit->id,
            'is_available' => false,
        ]);
    }

    public function test_invalid_borrowing_transitions_preserve_the_current_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Uji',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-14',
            'tracking_token' => 'state12345',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.handout', $borrowing))
            ->assertSessionHas('error');
        $this->assertSame('pending', $borrowing->fresh()->status);

        $this->actingAs($admin)->post(route('admin.asset-borrowings.approve', $borrowing));
        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.approve', $borrowing))
            ->assertSessionHas('error');
        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.reject', $borrowing), [
                'rejection_reason' => 'Sudah diproses',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('asset_borrowings', [
            'id' => $borrowing->id,
            'status' => 'approved',
            'rejection_reason' => null,
        ]);
    }

    public function test_handout_rejects_an_unavailable_unit_without_advancing_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Uji',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.0926.L1.LABUJI.009',
            'condition' => ConditionEnum::BAIK,
            'is_available' => false,
        ]);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Uji',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-14',
            'tracking_token' => 'unavail123',
        ]);
        $borrowing->status = 'approved';
        $borrowing->save();
        $borrowingItem = AssetBorrowingItem::create([
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.handout', $borrowing), [
                'unit_assignments' => [$borrowingItem->id => [$unit->id]],
            ])
            ->assertSessionHas('error');

        $this->assertSame('approved', $borrowing->fresh()->status);
        $this->assertNull($borrowingItem->fresh()->asset_unit_id);
        $this->assertFalse($unit->fresh()->is_available);
    }

    public function test_aggregate_handout_and_good_return_restore_the_original_balance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Kabel HDMI',
            'tracking_mode' => TrackingModeEnum::AGGREGATE,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);
        $balance = InventoryBalance::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'condition' => ConditionEnum::BAIK,
            'quantity' => 5,
        ]);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Uji',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-14',
            'tracking_token' => 'aggregate1',
        ]);
        $borrowing->status = 'approved';
        $borrowing->save();
        $borrowingItem = AssetBorrowingItem::create([
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'inventory_balance_id' => $balance->id,
            'quantity' => 2,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.handout', $borrowing))
            ->assertRedirect();
        $this->assertSame(3, $balance->fresh()->quantity);
        $this->assertSame('borrowed', $borrowing->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.receive', $borrowing), [
                'item_conditions' => [
                    $borrowingItem->id => ['condition' => 'BAIK'],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(5, $balance->fresh()->quantity);
        $this->assertSame('returned', $borrowing->fresh()->status);
        $this->assertSame('BAIK', $borrowingItem->fresh()->return_condition);
    }

    public function test_heavily_damaged_return_marks_the_unit_unavailable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Uji',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.0926.L1.LABUJI.010',
            'condition' => ConditionEnum::BAIK,
            'is_available' => false,
        ]);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Uji',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-14',
            'tracking_token' => 'damage1234',
        ]);
        $borrowing->status = 'borrowed';
        $borrowing->save();
        $borrowingItem = AssetBorrowingItem::create([
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'asset_unit_id' => $unit->id,
            'quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.asset-borrowings.receive', $borrowing), [
                'item_conditions' => [
                    $borrowingItem->id => [
                        'condition' => 'RUSAK_BERAT',
                        'notes' => 'Tidak dapat digunakan.',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('asset_units', [
            'id' => $unit->id,
            'condition' => 'RUSAK',
            'is_available' => false,
        ]);
    }

    public function test_lost_asset_return_requires_replacement_and_records_its_confirmation(): void
    {
        $this->travelTo('2026-10-14 09:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Uji',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '0926',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.0926.L1.LABUJI.003',
            'condition' => ConditionEnum::BAIK,
            'is_available' => false,
        ]);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Uji',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'borrower_address' => 'Alamat Uji',
            'lab_id' => $lab->id,
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-14',
            'tracking_token' => 'lost123456',
        ]);
        $borrowing->status = 'borrowed';
        $borrowing->save();
        $borrowingItem = AssetBorrowingItem::create([
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'asset_unit_id' => $unit->id,
            'quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.asset-borrowings.show', $borrowing))
            ->post(route('admin.asset-borrowings.receive', $borrowing), [
                'return_condition_notes' => 'Unit tidak dikembalikan.',
                'item_conditions' => [
                    $borrowingItem->id => [
                        'condition' => 'HILANG',
                        'notes' => 'Hilang saat perjalanan.',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.asset-borrowings.show', $borrowing));

        $borrowing->refresh();
        $this->assertSame('returned', $borrowing->status);
        $this->assertTrue($borrowing->is_damaged_on_return);
        $this->assertSame('2026-10-21', $borrowing->replacement_deadline->toDateString());
        $this->assertSame($admin->id, $borrowing->received_back_by);
        $this->assertDatabaseHas('asset_units', [
            'id' => $unit->id,
            'condition' => 'HILANG',
            'is_available' => false,
        ]);
        $this->assertDatabaseHas('asset_borrowing_items', [
            'id' => $borrowingItem->id,
            'return_condition' => 'HILANG',
            'return_notes' => 'Hilang saat perjalanan.',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.asset-borrowings.show', $borrowing))
            ->post(route('admin.asset-borrowings.confirm-replacement', $borrowing), [
                'replacement_notes' => 'Unit pengganti sudah diterima.',
            ])
            ->assertRedirect(route('admin.asset-borrowings.show', $borrowing));

        $borrowing->refresh();
        $this->assertTrue($borrowing->is_replaced);
        $this->assertSame($admin->id, $borrowing->replaced_by);
        $this->assertSame('Unit pengganti sudah diterima.', $borrowing->replacement_notes);
        $this->assertNotNull($borrowing->replaced_at);
    }
}
