<?php

namespace Tests\Feature;

use App\Models\AssetBorrowing;
use App\Models\Booking;
use App\Models\Lab;
use App\Models\User;
use App\Services\BorrowingDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportsAndDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_and_ajax_only_include_reportable_lab_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Laporan', 'capacity' => 40, 'status' => 'available']);
        $approved = $this->booking($lab, 'approved', 'Booking Dilaporkan', '2026-10-12');
        $this->booking($lab, 'pending', 'Booking Pending', '2026-10-13');

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'report_type' => 'lab',
                'booking_date_start' => '2026-10-01',
                'booking_date_end' => '2026-10-31',
            ]))
            ->assertOk()
            ->assertSee($approved->pic_name)
            ->assertDontSee('Booking Pending');

        $this->actingAs($admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.reports.index', ['report_type' => 'lab']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('html', fn (string $html): bool => str_contains($html, 'Booking Dilaporkan'));
    }

    public function test_admin_can_download_a_valid_excel_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Excel', 'capacity' => 40, 'status' => 'available']);
        $this->booking($lab, 'approved', 'Pemohon Excel', '2026-10-12');

        $response = $this->actingAs($admin)->get(route('admin.reports.export', [
            'report_type' => 'lab',
            'start_month' => '2026-10',
            'end_month' => '2026-10',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertSame('PK', file_get_contents($response->baseResponse->getFile()->getPathname(), false, null, 0, 2));
    }

    public function test_admin_can_download_a_word_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Word', 'capacity' => 40, 'status' => 'available']);
        $this->booking($lab, 'approved', 'Pemohon Word', '2026-10-12');

        $response = $this->actingAs($admin)->get(route('admin.reports.export-word', [
            'report_type' => 'lab',
            'start_month' => '2026-10',
            'end_month' => '2026-10',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.doc', (string) $response->headers->get('content-disposition'));
        $response->assertSee('Pemohon Word', false);
    }

    public function test_borrowing_document_service_generates_and_replaces_an_isolated_pdf(): void
    {
        Storage::fake('local');
        $this->travelTo('2026-10-12 09:00:00');
        $lab = Lab::create(['name' => 'Lab Dokumen', 'capacity' => 40, 'status' => 'available']);
        $borrowing = AssetBorrowing::create([
            'borrower_name' => 'Peminjam Dokumen',
            'borrower_type' => 'Mahasiswa',
            'borrower_id_number' => '12345678901234',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'purpose' => 'Pengujian dokumen',
            'borrow_date' => '2026-10-12',
            'return_date' => '2026-10-13',
            'tracking_token' => 'DOC1234567',
            'items_override' => [[
                'name' => 'Laptop',
                'brand_type' => 'ThinkPad',
                'quantity' => 2,
                'condition_good' => true,
                'condition_adequate' => false,
                'condition_complete' => true,
                'remarks' => 'Lengkap',
            ]],
        ]);

        $service = app(BorrowingDocumentService::class);
        $firstPath = $service->generatePDF($borrowing);

        Storage::disk('local')->assertExists('public/'.$firstPath);
        $this->assertSame('001/SPB/UPKFEB/X/2026', $borrowing->fresh()->document_number);
        $this->assertSame('12 Oktober 2026', $service->formatIndonesianDate('2026-10-12'));
        $this->assertSame(
            'Senin tanggal 12 bulan Oktober tahun 2026',
            $service->formatFullIndonesianDate('2026-10-12')
        );
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get('public/'.$firstPath));

        Storage::disk('local')->put('public/borrowing-documents/old.pdf', 'old');
        $borrowing->update(['generated_document_path' => 'borrowing-documents/old.pdf']);
        $secondPath = $service->generatePDF($borrowing->fresh());
        Storage::disk('local')->assertMissing('public/borrowing-documents/old.pdf');
        Storage::disk('local')->assertExists('public/'.$secondPath);
        $this->assertSame('001/SPB/UPKFEB/X/2026', $borrowing->fresh()->document_number);
    }

    private function booking(Lab $lab, string $status, string $picName, string $date): Booking
    {
        $booking = Booking::create([
            'lab_id' => $lab->id,
            'booking_type' => 'perkuliahan_tidak_tetap',
            'pic_name' => $picName,
            'phone_number' => '081234567890',
            'day' => 'Senin',
            'booking_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'tracking_token' => str()->uuid()->toString(),
        ]);
        $booking->forceFill(['status' => $status])->save();

        return $booking;
    }
}
