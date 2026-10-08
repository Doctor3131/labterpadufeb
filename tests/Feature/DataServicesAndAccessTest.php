<?php

namespace Tests\Feature;

use App\Models\BloombergRequest;
use App\Models\BpsMasterData;
use App\Models\BpsRequest;
use App\Models\BpsSubData;
use App\Models\MahasiswaFeb;
use App\Models\RefinitivRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataServicesAndAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_bps_request_persists_selected_dataset_and_documents(): void
    {
        Storage::fake('public');
        $master = BpsMasterData::create(['name' => 'Sensus Uji', 'code' => 'SUJI']);
        $subData = BpsSubData::create(['master_id' => $master->id, 'name' => 'Data Uji']);

        $response = $this->post(route('bps.store'), [
            'applicant_type' => 'mahasiswa',
            'name' => 'Mahasiswa Uji',
            'email' => 'mahasiswa@example.test',
            'phone' => '081234567890',
            'purpose' => 'Riset',
            'has_lecturer_collaboration' => '0',
            'selected_data' => [$subData->id],
            'variables' => [$subData->id => 'A1, B2'],
            'statement_letter' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
            'agreement_accepted' => '1',
            'nim' => '12345678901234',
            'study_program' => 'S1- Manajemen',
            'ktm' => UploadedFile::fake()->create('ktm.pdf', 10, 'application/pdf'),
        ]);

        $request = BpsRequest::firstOrFail();

        $response->assertRedirect(route('bps.success', $request->token));
        $this->assertSame('pending', $request->status);
        $this->assertTrue($request->subData->contains($subData));
        $this->assertDatabaseHas('bps_request_variables', [
            'request_id' => $request->id,
            'sub_data_id' => $subData->id,
            'variables' => 'A1, B2',
        ]);
        Storage::disk('public')->assertExists($request->statement_letter_path);
        Storage::disk('public')->assertExists($request->ktm_path);
    }

    public function test_refinitiv_student_request_is_saved_with_pending_attendance(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 8)->setTime(9, 0));
        Storage::fake('public');

        $response = $this->post(route('refinitiv.store'), [
            'name' => 'Mahasiswa Uji',
            'whatsapp' => '081234567890',
            'affiliation' => 'internal_feb',
            'applicant_type' => 'mahasiswa',
            'purpose' => 'skripsi',
            'usage_date' => '2026-10-09',
            'session' => 'sesi_1',
            'variables' => 'PDRB, tenaga kerja',
            'statement_file' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
            'agreement' => '1',
            'understood' => '1',
            'nim' => '12345678901234',
            'study_program' => 'S1- Manajemen',
            'ktm_file' => UploadedFile::fake()->create('ktm.pdf', 10, 'application/pdf'),
        ]);

        $request = RefinitivRequest::firstOrFail();

        $response->assertRedirect(route('refinitiv.success', $request->token));
        $this->assertSame('12345678901234', $request->nim_nip);
        $this->assertSame('pending', $request->attendance_status);
        Storage::disk('public')->assertExists($request->statement_file);
        Storage::disk('public')->assertExists($request->ktm_file);
    }

    public function test_bloomberg_reservation_persists_request_and_document(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 8)->setTime(9, 0));
        Storage::fake('public');

        $response = $this->post(route('bloomberg.store'), [
            'type' => 'reservasi',
            'name' => 'Mahasiswa Uji',
            'phone' => '081234567890',
            'applicant_type' => 'mahasiswa',
            'usage_date' => '2026-10-09',
            'session' => 'sesi_1',
            'purpose' => 'explore',
            'statement_file' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
            'agreement_citation' => '1',
            'agreement_compliance' => '1',
            'nim' => '12345678901234',
            'study_program' => 'S1- Manajemen',
        ]);

        $request = BloombergRequest::firstOrFail();

        $response->assertRedirect(route('bloomberg.success', $request->token));
        $this->assertSame('reservasi', $request->type);
        $this->assertSame('explore', $request->purpose);
        $this->assertSame('2026-10-09', $request->usage_date->toDateString());
        Storage::disk('public')->assertExists($request->statement_file);
    }

    public function test_personal_borrowing_nim_lookup_reports_known_and_unknown_students(): void
    {
        MahasiswaFeb::create([
            'nim' => '12345678901234',
            'nama' => 'Mahasiswa Uji',
            'prodi' => 'Manajemen',
        ]);

        $this->postJson(route('personal-borrowing.validate-nim'), ['nim' => '12345678901234'])
            ->assertOk()
            ->assertJson(['found' => true]);

        $this->postJson(route('personal-borrowing.validate-nim'), ['nim' => '99999999999999'])
            ->assertOk()
            ->assertJson(['found' => false]);
    }

    public function test_feedback_submission_is_persisted_as_pending(): void
    {
        $response = $this->post(route('feedback.store'), [
            'title' => 'Lampu ruang kelas mati',
            'detail' => 'Lampu di ruang kelas perlu diperiksa.',
        ]);

        $response->assertRedirect(route('landing'));
        $this->assertDatabaseHas('feedbacks', [
            'title' => 'Lampu ruang kelas mati',
            'status' => 'pending',
        ]);
    }

    public function test_admin_and_super_admin_routes_enforce_their_access_tiers(): void
    {
        $this->get(route('admin.inventory.index'))->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
    }
}
