<?php

namespace Tests\Feature;

use App\Models\BloombergRequest;
use App\Models\BpsMasterData;
use App\Models\BpsSubData;
use App\Models\RefinitivRequest;
use App\Models\ServiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataServiceConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_walk_in_policy_rejects_crafted_submissions(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        Storage::fake('public');
        ServiceSetting::setValue('bloomberg', 'walk_in_enabled', '0');
        $payload = $this->validBloombergPayload();
        $payload['type'] = 'walk_in';

        $this->from(route('bloomberg.walkin'))
            ->post(route('bloomberg.store'), $payload)
            ->assertRedirect(route('bloomberg.walkin'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('bloomberg_requests', 0);
    }

    public function test_bloomberg_success_page_requires_the_opaque_token(): void
    {
        $request = $this->createBloombergRequest('2026-10-12', 'sesi_1');

        $this->get(route('bloomberg.success', $request->token))->assertOk();
        $this->get(route('bloomberg.success', $request->id))->assertNotFound();
    }

    public function test_bps_submission_rejects_an_inactive_catalog_selection(): void
    {
        Storage::fake('public');
        $master = BpsMasterData::create([
            'name' => 'Sensus Nonaktif',
            'code' => 'OFF',
            'is_active' => false,
        ]);
        $subData = BpsSubData::create([
            'master_id' => $master->id,
            'name' => 'Data Nonaktif',
            'is_active' => false,
        ]);

        $this->from(route('bps.create'))
            ->post(route('bps.store'), [
                'applicant_type' => 'mahasiswa',
                'name' => 'Mahasiswa Uji',
                'email' => 'mahasiswa@example.test',
                'phone' => '081234567890',
                'purpose' => 'Riset',
                'has_lecturer_collaboration' => '0',
                'selected_data' => [$subData->id],
                'variables' => [$subData->id => 'A1'],
                'statement_letter' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
                'agreement_accepted' => '1',
                'nim' => '12345678901234',
                'study_program' => 'S1- Manajemen',
                'ktm' => UploadedFile::fake()->create('ktm.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('bps.create'))
            ->assertSessionHasErrors('selected_data.0');

        $this->assertDatabaseCount('bps_requests', 0);
    }

    public function test_refinitiv_student_submission_requires_student_documents(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        Storage::fake('public');

        $this->from(route('refinitiv.create'))
            ->post(route('refinitiv.store'), [
                'name' => 'Mahasiswa Uji',
                'whatsapp' => '081234567890',
                'affiliation' => 'internal_feb',
                'applicant_type' => 'mahasiswa',
                'purpose' => 'skripsi',
                'usage_date' => '2026-10-12',
                'session' => 'sesi_1',
                'variables' => 'PDRB',
                'statement_file' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
                'agreement' => '1',
                'understood' => '1',
            ])
            ->assertRedirect(route('refinitiv.create'))
            ->assertSessionHasErrors(['nim', 'study_program', 'ktm_file']);

        $this->assertDatabaseCount('refinitiv_requests', 0);
    }

    public function test_refinitiv_rejects_sunday_usage(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        Storage::fake('public');

        $this->from(route('refinitiv.create'))
            ->post(route('refinitiv.store'), [
                'name' => 'Mahasiswa Uji',
                'whatsapp' => '081234567890',
                'affiliation' => 'internal_feb',
                'applicant_type' => 'mahasiswa',
                'purpose' => 'skripsi',
                'usage_date' => '2026-10-11',
                'session' => 'sesi_1',
                'variables' => 'PDRB',
                'statement_file' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
                'agreement' => '1',
                'understood' => '1',
                'nim' => '12345678901234',
                'study_program' => 'S1- Manajemen',
                'ktm_file' => UploadedFile::fake()->create('ktm.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('refinitiv.create'))
            ->assertSessionHasErrors('usage_date');

        $this->assertDatabaseCount('refinitiv_requests', 0);
    }

    public function test_refinitiv_lecturer_submission_requires_nip_but_not_ktm(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        Storage::fake('public');

        $response = $this->post(route('refinitiv.store'), [
            'name' => 'Dosen Uji',
            'whatsapp' => '081234567890',
            'affiliation' => 'internal_feb',
            'applicant_type' => 'dosen',
            'purpose' => 'penelitian_dosen',
            'lecturer_name' => 'Dosen Uji',
            'usage_date' => '2026-10-12',
            'session' => 'sesi_2',
            'variables' => 'PDRB',
            'statement_file' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
            'agreement' => '1',
            'understood' => '1',
            'nip' => '123456789012345678',
        ]);

        $request = RefinitivRequest::firstOrFail();
        $response->assertRedirect(route('refinitiv.success', $request->token));
        $this->assertSame('dosen', $request->applicant_type);
        $this->assertSame('123456789012345678', $request->nim_nip);
        $this->assertNull($request->ktm_file);
        $this->get(route('refinitiv.success', $request->token))->assertOk();
        $this->get(route('refinitiv.success', $request->id))->assertNotFound();
    }

    public function test_admin_can_mark_refinitiv_request_as_not_attending(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = RefinitivRequest::create([
            'token' => str_repeat('n', 64),
            'name' => 'Mahasiswa Uji',
            'nim_nip' => '12345678901234',
            'whatsapp' => '081234567890',
            'affiliation' => 'internal_feb',
            'applicant_type' => 'mahasiswa',
            'study_program' => 'S1- Manajemen',
            'purpose' => 'skripsi',
            'usage_date' => '2026-10-12',
            'session' => 'sesi_1',
            'variables' => 'PDRB',
            'statement_file' => 'refinitiv/surat.pdf',
            'attendance_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.refinitiv.show', $request))
            ->put(route('admin.refinitiv.tidak-hadir', $request))
            ->assertRedirect(route('admin.refinitiv.show', $request));

        $request->refresh();
        $this->assertSame('tidak_hadir', $request->attendance_status);
        $this->assertSame($admin->id, $request->handled_by);
        $this->assertNotNull($request->attendance_marked_at);
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
