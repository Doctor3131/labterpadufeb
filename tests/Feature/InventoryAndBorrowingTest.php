<?php

namespace Tests\Feature;

use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use App\Models\AssetBorrowing;
use App\Models\AssetBorrowingItem;
use App\Models\AssetUnit;
use App\Models\Batch;
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
}
