<?php

namespace Tests\Feature;

use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use App\Models\Announcement;
use App\Models\AssetUnit;
use App\Models\Batch;
use App\Models\Booking;
use App\Models\BpsMasterData;
use App\Models\BpsSubData;
use App\Models\Feedback;
use App\Models\Item;
use App\Models\Lab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCrudWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_toggle_a_lab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.labs.store'), [
            'name' => 'Lab Baru',
            'capacity' => 35,
            'description' => 'Laboratorium baru',
        ])->assertRedirect(route('admin.labs.index'));

        $lab = Lab::where('name', 'Lab Baru')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.labs.update', $lab), [
            'name' => 'Lab Diperbarui',
            'capacity' => 40,
            'description' => 'Deskripsi baru',
        ])->assertRedirect(route('admin.labs.index'));
        $this->actingAs($admin)
            ->post(route('admin.labs.toggle-status', $lab))
            ->assertRedirect(route('admin.labs.index'));

        $this->assertDatabaseHas('labs', [
            'id' => $lab->id,
            'name' => 'Lab Diperbarui',
            'capacity' => 40,
            'status' => 'inactive',
        ]);
    }

    public function test_lab_with_a_booking_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Terpakai', 'capacity' => 40, 'status' => 'available']);
        Booking::create([
            'lab_id' => $lab->id,
            'booking_type' => 'perkuliahan_tidak_tetap',
            'pic_name' => 'Pemohon',
            'phone_number' => '081234567890',
            'day' => 'Senin',
            'booking_date' => '2026-10-12',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'tracking_token' => str_repeat('l', 32),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.labs.destroy', $lab))
            ->assertRedirect(route('admin.labs.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('labs', ['id' => $lab->id]);
    }

    public function test_admin_can_create_toggle_update_and_delete_an_announcement(): void
    {
        $this->travelTo('2026-10-01 09:00:00');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Pemeliharaan Sistem',
            'content' => 'Sistem akan dipelihara.',
            'type' => 'info',
            'expires_at' => '2026-10-10 09:00:00',
        ])->assertRedirect(route('admin.announcements.index'));

        $announcement = Announcement::firstOrFail();
        $this->assertTrue($announcement->is_active);
        $this->assertSame($admin->id, $announcement->created_by);

        $this->actingAs($admin)
            ->post(route('admin.announcements.toggle-active', $announcement))
            ->assertRedirect(route('admin.announcements.index'));
        $this->assertFalse($announcement->fresh()->is_active);

        $this->actingAs($admin)->put(route('admin.announcements.update', $announcement), [
            'title' => 'Pemeliharaan Selesai',
            'content' => 'Sistem kembali tersedia.',
            'type' => 'penting',
            'expires_at' => '2026-10-11 09:00:00',
        ])->assertRedirect(route('admin.announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Pemeliharaan Selesai',
            'type' => 'penting',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.announcements.destroy', $announcement))
            ->assertRedirect(route('admin.announcements.index'));
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_admin_can_review_and_resolve_feedback_with_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = Feedback::create([
            'title' => 'Lampu mati',
            'detail' => 'Lampu perlu diganti.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.feedbacks.index'))
            ->put(route('admin.feedbacks.update', $feedback), [
                'status' => 'resolved',
                'admin_notes' => 'Lampu telah diganti.',
            ])
            ->assertRedirect(route('admin.feedbacks.index'));

        $this->assertDatabaseHas('feedbacks', [
            'id' => $feedback->id,
            'status' => 'resolved',
            'admin_notes' => 'Lampu telah diganti.',
        ]);
    }

    public function test_super_admin_can_create_and_reset_an_admin_but_cannot_delete_self(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@example.test',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'role' => 'admin',
        ])->assertRedirect(route('admin.users.index'));

        $admin = User::where('email', 'admin.baru@example.test')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('StrongPass123', $admin->password));

        $this->actingAs($superAdmin)->put(route('admin.users.reset-password', $admin), [
            'new_password' => 'NewStrong456',
        ])->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('NewStrong456', $admin->fresh()->password));

        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_external_transfer_rejects_a_unit_that_is_not_in_gudang(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Lab::create(['name' => 'Gudang', 'capacity' => 100, 'status' => 'available']);
        $sourceLab = Lab::create(['name' => 'Lab Asal', 'capacity' => 40, 'status' => 'available']);
        $item = Item::create([
            'name' => 'Laptop Lab',
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
            'asset_tag' => 'LAB-ASAL-001',
            'condition' => ConditionEnum::BAIK,
            'is_available' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.external-transfers.create'))
            ->post(route('admin.external-transfers.store'), [
                'item_id' => $item->id,
                'recipient' => 'Mitra Eksternal',
                'transfer_date' => '2026-10-12',
                'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG->value,
                'unit_ids' => [$unit->id],
            ])
            ->assertRedirect(route('admin.external-transfers.create'))
            ->assertSessionHas('error');

        $this->assertSame($sourceLab->id, $unit->fresh()->lab_id);
        $this->assertDatabaseCount('external_transfers', 0);
    }

    public function test_bps_sub_data_cannot_be_mutated_through_another_master_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstMaster = BpsMasterData::create(['name' => 'Master Pertama', 'code' => 'ONE']);
        $secondMaster = BpsMasterData::create(['name' => 'Master Kedua', 'code' => 'TWO']);
        $subData = BpsSubData::create([
            'master_id' => $secondMaster->id,
            'name' => 'Data Master Kedua',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.bps.sub-data.update', [$firstMaster, $subData]), [
                'name' => 'Mutasi Tidak Sah',
                'description' => 'Tidak boleh berpindah konteks.',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('bps_sub_data', [
            'id' => $subData->id,
            'master_id' => $secondMaster->id,
            'name' => 'Data Master Kedua',
        ]);
    }
}
