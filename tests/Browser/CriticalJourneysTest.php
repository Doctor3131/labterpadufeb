<?php

namespace Tests\Browser;

use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use App\Models\AssetBorrowing;
use App\Models\AssetBorrowingItem;
use App\Models\AssetUnit;
use App\Models\Batch;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Lab;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CriticalJourneysTest extends DuskTestCase
{
    private User $admin;

    private array $uploadedPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Pengujian',
            'email' => 'dusk-admin@example.test',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->uploadedPaths as $path) {
            Storage::disk('public')->delete($path);
        }

        parent::tearDown();
    }

    public function test_public_booking_can_be_submitted_and_approved_by_an_admin(): void
    {
        $lab = Lab::create(['name' => 'Lab Dusk', 'capacity' => 40, 'status' => 'available']);

        $this->browse(function (Browser $browser) use ($lab) {
            $browser->visit('/booking')
                ->assertPresent('#bookingForm')
                ->click('label.booking-type-card:nth-of-type(2)')
                ->click('input[name="unit_type"][value="s1_tembalang"]')
                ->click('#btn-next-1')
                ->waitForText('Data Pengaju & Kegiatan', 10)
                ->type('#pic_name', 'Mahasiswa Dusk');

            $browser->script("const select = document.querySelector('#study_program'); select.value = 'S1- Manajemen'; select.dispatchEvent(new Event('input', { bubbles: true })); select.dispatchEvent(new Event('change', { bubbles: true }));");
            $browser->assertSelected('#study_program', 'S1- Manajemen')
                ->type('#nim', '12345678901234')
                ->type('#phone_number', '081234567890')
                ->type('#course_name', 'Praktik Dusk')
                ->type('#lecturer_name', 'Dosen Dusk')
                ->type('#lecturer_nip', '123456789012345678')
                ->click('#btn-next-2')
                ->waitForText('Pilih Jadwal & Laboratorium', 10);

            $bookingDate = now()->addDays(14);
            while ($bookingDate->isSunday()) {
                $bookingDate->addDay();
            }

            $browser->script(str_replace(
                ['__LAB_ID__', '__BOOKING_DATE__'],
                [(string) $lab->id, $bookingDate->toDateString()],
                <<<'JS'
                    const values = {
                        labSelect: '__LAB_ID__',
                        booking_date: '__BOOKING_DATE__',
                        participant_count: '20',
                        start_hour: '08',
                        start_minute: '00',
                        end_hour: '10',
                        end_minute: '00',
                    };
                    for (const [id, value] of Object.entries(values)) {
                        const field = document.getElementById(id);
                        field.value = value;
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        field.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    JS
            ));

            $browser->waitUntilEnabled('#btn-next-3', 15)
                ->click('#btn-next-3')
                ->waitForText('Tinjau & Kirim Pengajuan', 10)
                ->attach('#document', base_path('tests/Fixtures/supporting-document.pdf'))
                ->click('#btn-submit')
                ->waitForText('Peminjaman Berhasil Diajukan!', 15);

            $booking = Booking::query()->latest('id')->firstOrFail();
            $this->uploadedPaths[] = $booking->document_path;

            $this->assertTrue(Storage::disk('public')->exists($booking->document_path));
            $this->assertSame('pending', $booking->status);
            $this->loginAsAdmin($browser);
            $browser->visit(route('admin.booking.show', $booking->id))
                ->waitFor('form[action$="/approve"] button', 10);

            $browser->script('window.confirm = () => true;');
            $browser->click('form[action$="/approve"] button')
                ->waitForText('Disetujui', 10);

            $this->assertDatabaseHas('bookings', [
                'id' => $booking->id,
                'status' => 'approved',
                'handled_by' => $this->admin->id,
            ]);
        });
    }

    public function test_admin_can_approve_and_hand_out_a_borrowed_asset(): void
    {
        [, , $unit, $borrowing] = $this->structuredAssetBorrowing();

        $this->browse(function (Browser $browser) use ($borrowing, $unit) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.asset-borrowings.show', $borrowing->id))
                ->click('form[action$="/approve"] button')
                ->click('#approveConfirmSubmitBtn')
                ->waitForText('Serahkan Barang', 10)
                ->click('button[onclick="openHandoutModal()"]')
                ->waitFor('.unit-select-show', 10)
                ->select('.unit-select-show', (string) $unit->id)
                ->click('#submitHandoutBtn')
                ->waitForText('Sedang Dipinjam', 10);

            $this->assertDatabaseHas('asset_borrowings', [
                'id' => $borrowing->id,
                'status' => 'borrowed',
                'handed_out_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('asset_units', [
                'id' => $unit->id,
                'is_available' => false,
            ]);
        });
    }

    public function test_admin_can_change_an_asset_condition_from_the_inventory_page(): void
    {
        $lab = Lab::create(['name' => 'Lab Dusk', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Dusk',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '1026',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.1026.L1.LABDUSK.001',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);

        $this->browse(function (Browser $browser) use ($lab, $item, $unit) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.labs.inventory.units', [$lab->id, $item->id]))
                ->check("table input[type=checkbox][value='{$unit->id}']")
                ->waitFor('#bulkConditionForm')
                ->select('#bulkConditionForm select[name=condition]', 'RUSAK')
                ->type('#bulkConditionForm input[name=notes]', 'Dicatat dari uji browser')
                ->click('#bulkConditionForm button[type=submit]')
                ->waitForText('Rusak', 10);

            $this->assertDatabaseHas('asset_units', [
                'id' => $unit->id,
                'condition' => 'RUSAK',
                'is_available' => false,
                'notes' => 'Dicatat dari uji browser',
            ]);
        });
    }

    private function loginAsAdmin(Browser $browser): void
    {
        $browser->visit('/login');
        $browser->driver->manage()->deleteAllCookies();
        $browser->visit('/login')
            ->waitForLocation('/login', 10)
            ->type('email', $this->admin->email)
            ->type('password', 'password')
            ->press('Masuk')
            ->waitForLocation('/admin/dashboard', 10);
    }

    /**
     * @return array{Lab, Item, AssetUnit, AssetBorrowing, AssetBorrowingItem}
     */
    private function structuredAssetBorrowing(): array
    {
        $lab = Lab::create(['name' => 'Lab Dusk', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Dusk',
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $batch = Batch::create([
            'item_id' => $item->id,
            'proc_source_code' => '01',
            'arrival_mmyy' => '1026',
        ]);
        $unit = AssetUnit::create([
            'batch_id' => $batch->id,
            'lab_id' => $lab->id,
            'asset_tag' => '01.1026.L1.LABDUSK.002',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Dusk',
            'borrower_type' => 'Mahasiswa',
            'phone_number' => '081234567890',
            'borrower_address' => 'Alamat Dusk',
            'lab_id' => $lab->id,
            'purpose' => 'Kegiatan akademik',
            'borrow_date' => now()->addDays(7)->toDateString(),
            'return_date' => now()->addDays(8)->toDateString(),
            'generated_document_path' => 'dusk/borrowing-document.pdf',
        ]);
        $borrowing->tracking_token = 'dusk000001';
        $borrowing->status = 'pending';
        $borrowing->save();
        $borrowingItem = AssetBorrowingItem::create([
            'asset_borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        return [$lab, $item, $unit, $borrowing, $borrowingItem];
    }
}
