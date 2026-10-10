<?php

namespace Tests\Browser;

use App\Models\BlockedDate;
use App\Models\BloombergRequest;
use App\Models\ServiceSetting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\DuskTestCase;

class MySqlDataServiceRulesTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_bloomberg_capacity_is_isolated_by_date_and_session_on_mysql(): void
    {
        ServiceSetting::setValue('bloomberg', 'capacity_per_session', '1');
        $this->createBloombergRequest('2026-10-12', 'sesi_1');

        $this->getJson(route('bloomberg.capacity', ['date' => '2026-10-12', 'session' => 'sesi_1']))
            ->assertOk()
            ->assertJson(['remaining' => 0, 'capacity' => 1, 'full' => true]);
        $this->getJson(route('bloomberg.capacity', ['date' => '2026-10-12', 'session' => 'sesi_2']))
            ->assertOk()
            ->assertJson(['remaining' => 1, 'capacity' => 1, 'full' => false]);
        $this->getJson(route('bloomberg.capacity', ['date' => '2026-10-13', 'session' => 'sesi_1']))
            ->assertOk()
            ->assertJson(['remaining' => 1, 'capacity' => 1, 'full' => false]);
    }

    public function test_full_bloomberg_session_rejects_another_reservation_on_mysql(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        Storage::fake('public');
        ServiceSetting::setValue('bloomberg', 'capacity_per_session', '1');
        $this->createBloombergRequest('2026-10-12', 'sesi_1');

        $this->from(route('bloomberg.create'))
            ->post(route('bloomberg.store'), $this->validBloombergPayload())
            ->assertRedirect(route('bloomberg.create'))
            ->assertSessionHas('error', 'Sesi yang dipilih sudah penuh untuk tanggal tersebut.');

        $this->assertDatabaseCount('bloomberg_requests', 1);
    }

    public function test_session_specific_and_entire_day_blocks_are_enforced_on_mysql(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        BlockedDate::create([
            'service_type' => 'bloomberg',
            'blocked_date' => '2026-10-12',
            'reason' => 'Pemeliharaan pagi',
            'blocked_session' => 'sesi_1',
            'created_by' => $admin->id,
        ]);

        $this->from(route('bloomberg.create'))
            ->post(route('bloomberg.store'), $this->validBloombergPayload())
            ->assertRedirect(route('bloomberg.create'))
            ->assertSessionHasErrors('usage_date');

        $allowedPayload = $this->validBloombergPayload();
        $allowedPayload['session'] = 'sesi_2';
        $this->post(route('bloomberg.store'), $allowedPayload)->assertRedirect();
        $this->assertDatabaseCount('bloomberg_requests', 1);

        $this->actingAs($admin)
            ->from(route('admin.bloomberg.blocked-dates'))
            ->post(route('admin.bloomberg.blocked-dates.store'), [
                'blocked_date' => '2026-10-12',
                'reason' => 'Pemeliharaan sehari penuh',
                'blocked_session' => null,
            ])
            ->assertRedirect(route('admin.bloomberg.blocked-dates'));

        $this->assertDatabaseCount('blocked_dates', 1);
        $this->assertDatabaseHas('blocked_dates', [
            'service_type' => 'bloomberg',
            'blocked_date' => '2026-10-12',
            'blocked_session' => null,
        ]);
        $this->assertTrue(BlockedDate::isBlocked('bloomberg', '2026-10-12', 'sesi_1'));
        $this->assertTrue(BlockedDate::isBlocked('bloomberg', '2026-10-12', 'sesi_2'));

        $this->actingAs($admin)
            ->from(route('admin.bloomberg.blocked-dates'))
            ->post(route('admin.bloomberg.blocked-dates.store'), [
                'blocked_date' => '2026-10-12',
                'reason' => 'Duplikat',
                'blocked_session' => null,
            ])
            ->assertRedirect(route('admin.bloomberg.blocked-dates'))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('blocked_dates', 1);
    }

    private function createBloombergRequest(string $date, string $session): BloombergRequest
    {
        return BloombergRequest::create([
            'token' => bin2hex(random_bytes(16)),
            'type' => 'reservasi',
            'name' => 'Mahasiswa Uji',
            'nim_nip' => '12345678901234',
            'phone' => '081234567890',
            'applicant_type' => 'mahasiswa',
            'study_program' => 'S1- Manajemen',
            'university' => 'Universitas Diponegoro',
            'usage_date' => $date,
            'session' => $session,
            'purpose' => 'explore',
            'statement_file' => 'bloomberg/surat.pdf',
        ]);
    }

    private function validBloombergPayload(): array
    {
        return [
            'type' => 'reservasi',
            'name' => 'Mahasiswa Baru',
            'phone' => '081234567891',
            'applicant_type' => 'mahasiswa',
            'usage_date' => '2026-10-12',
            'session' => 'sesi_1',
            'purpose' => 'explore',
            'statement_file' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
            'agreement_citation' => '1',
            'agreement_compliance' => '1',
            'nim' => '12345678901235',
            'study_program' => 'S1- Manajemen',
        ];
    }
}
