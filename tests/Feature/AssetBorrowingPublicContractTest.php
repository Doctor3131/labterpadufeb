<?php

namespace Tests\Feature;

use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use App\Models\AssetBorrowing;
use App\Models\AssetTypeCode;
use App\Models\AssetUnit;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Lab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetBorrowingPublicContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_request_allocates_available_units_and_uses_an_opaque_token(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        [$item, $unit] = $this->createStructuredInventory(true);

        $response = $this->post(route('asset-borrowing.store'), $this->validPayload($item, 1));

        $borrowing = AssetBorrowing::firstOrFail();
        $response->assertRedirect(route('asset-borrowing.success', $borrowing->tracking_token));
        $this->assertSame('pending', $borrowing->status);
        $this->assertSame(10, strlen($borrowing->tracking_token));
        $this->assertDatabaseHas('asset_borrowing_items', [
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'asset_unit_id' => $unit->id,
            'quantity' => 1,
        ]);
        $this->get(route('asset-borrowing.success', $borrowing->tracking_token))->assertOk();
        $this->get(route('asset-borrowing.success', $borrowing->id))->assertNotFound();
    }

    public function test_insufficient_stock_rejects_the_whole_request(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        [$item] = $this->createStructuredInventory(true);

        $this->from(route('asset-borrowing.create'))
            ->post(route('asset-borrowing.store'), $this->validPayload($item, 2))
            ->assertRedirect(route('asset-borrowing.create'))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseCount('asset_borrowings', 0);
        $this->assertDatabaseCount('asset_borrowing_items', 0);
    }

    public function test_non_borrowable_items_are_rejected_even_when_units_are_available(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        [$item] = $this->createStructuredInventory(false);

        $this->from(route('asset-borrowing.create'))
            ->post(route('asset-borrowing.store'), $this->validPayload($item, 1))
            ->assertRedirect(route('asset-borrowing.create'))
            ->assertSessionHasErrors('items.0.item_id');

        $this->assertDatabaseCount('asset_borrowings', 0);
    }

    /** @return array{Item, AssetUnit} */
    private function createStructuredInventory(bool $isBorrowable): array
    {
        $lab = Lab::create(['name' => 'Lab Peminjaman', 'capacity' => 40, 'status' => 'available']);
        $typeCode = AssetTypeCode::create([
            'code' => $isBorrowable ? 'BRW' : 'LOCK',
            'name' => $isBorrowable ? 'Barang Pinjam' : 'Barang Tetap',
            'default_tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
            'is_borrowable' => $isBorrowable,
        ]);
        $item = Item::create([
            'name' => $isBorrowable ? 'Laptop Pinjam' : 'Komputer Tetap',
            'asset_type_code_id' => $typeCode->id,
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
            'asset_tag' => '01.0926.'.$typeCode->code.'.LAB.001',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);

        return [$item, $unit];
    }

    private function validPayload(Item $item, int $quantity): array
    {
        return [
            'borrower_name' => 'Mahasiswa Uji',
            'borrower_type' => 'Mahasiswa',
            'borrower_id_number' => '12345678901234',
            'study_program' => 'Manajemen',
            'class_year' => '2024',
            'phone_number' => '081234567890',
            'email' => 'mahasiswa@example.test',
            'borrower_address' => 'Alamat mahasiswa uji',
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-14',
            'borrow_time' => '08:00',
            'return_time' => '10:00',
            'items' => [[
                'item_id' => $item->id,
                'quantity' => $quantity,
                'condition_good' => '1',
                'condition_complete' => '1',
            ]],
        ];
    }
}
