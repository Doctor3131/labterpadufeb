<?php

namespace Tests\Feature;

use App\Models\BpsRequest;
use App\Models\RefinitivRequest;
use App\Models\ServiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDataServiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_complete_a_pending_bps_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = BpsRequest::create([
            'token' => str_repeat('b', 64),
            'applicant_type' => 'mahasiswa',
            'name' => 'Mahasiswa Uji',
            'email' => 'mahasiswa@example.test',
            'nim' => '12345678901234',
            'phone' => '081234567890',
            'study_program' => 'S1- Manajemen',
            'purpose' => 'Riset',
            'statement_letter_path' => 'bps/surat.pdf',
            'agreement_accepted' => true,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.bps.requests.show', $request))
            ->put(route('admin.bps.requests.complete', $request))
            ->assertRedirect(route('admin.bps.requests.show', $request));

        $request->refresh();
        $this->assertSame('completed', $request->status);
        $this->assertSame($admin->id, $request->handled_by);
        $this->assertNotNull($request->completed_at);
    }

    public function test_admin_can_record_and_reset_refinitiv_attendance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = RefinitivRequest::create([
            'token' => str_repeat('r', 64),
            'name' => 'Mahasiswa Uji',
            'nim_nip' => '12345678901234',
            'whatsapp' => '081234567890',
            'affiliation' => 'internal_feb',
            'applicant_type' => 'mahasiswa',
            'study_program' => 'S1- Manajemen',
            'purpose' => 'skripsi',
            'usage_date' => '2026-10-09',
            'session' => 'sesi_1',
            'variables' => 'PDRB',
            'statement_file' => 'refinitiv/surat.pdf',
            'attendance_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.refinitiv.show', $request))
            ->put(route('admin.refinitiv.hadir', $request))
            ->assertRedirect(route('admin.refinitiv.show', $request));

        $request->refresh();
        $this->assertSame('hadir', $request->attendance_status);
        $this->assertSame($admin->id, $request->handled_by);
        $this->assertNotNull($request->attendance_marked_at);

        $this->actingAs($admin)
            ->from(route('admin.refinitiv.show', $request))
            ->put(route('admin.refinitiv.reset', $request))
            ->assertRedirect(route('admin.refinitiv.show', $request));

        $request->refresh();
        $this->assertSame('pending', $request->attendance_status);
        $this->assertNull($request->handled_by);
        $this->assertNull($request->attendance_marked_at);
    }

    public function test_admin_can_update_bloomberg_capacity_and_walk_in_policy(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.bloomberg.settings'))
            ->put(route('admin.bloomberg.settings.update'), [
                'capacity_per_session' => 18,
                'walk_in_enabled' => '1',
            ])
            ->assertRedirect(route('admin.bloomberg.settings'));

        $this->assertSame('18', ServiceSetting::getValue('bloomberg', 'capacity_per_session'));
        $this->assertTrue(ServiceSetting::isEnabled('bloomberg', 'walk_in_enabled'));
        $this->assertDatabaseHas('service_settings', [
            'service_type' => 'bloomberg',
            'key' => 'capacity_per_session',
            'updated_by' => $admin->id,
        ]);
    }

    public function test_admin_cannot_block_a_past_bloomberg_date(): void
    {
        $this->travelTo('2026-10-10 09:00:00');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.bloomberg.blocked-dates'))
            ->post(route('admin.bloomberg.blocked-dates.store'), [
                'blocked_date' => '2026-10-09',
                'reason' => 'Tanggal lampau',
                'blocked_session' => null,
            ])
            ->assertRedirect(route('admin.bloomberg.blocked-dates'))
            ->assertSessionHasErrors('blocked_date');

        $this->assertDatabaseCount('blocked_dates', 0);
    }
}
