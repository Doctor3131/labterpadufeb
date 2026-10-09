<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Lab;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_approval_creates_a_schedule_and_records_the_handler(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = $this->createPendingBooking();

        $this->actingAs($admin)
            ->post(route('admin.booking.approve', $booking), ['return_status' => 'pending'])
            ->assertRedirect(route('admin.lab.bookings', ['status' => 'pending']));

        $booking->refresh();
        $this->assertSame('approved', $booking->status);
        $this->assertSame($admin->id, $booking->handled_by);
        $this->assertNotNull($booking->handled_at);
        $this->assertDatabaseHas('schedules', [
            'booking_id' => $booking->id,
            'lab_id' => $booking->lab_id,
            'start_date' => '2026-10-12 00:00:00',
            'end_date' => '2026-10-12 00:00:00',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);
    }

    public function test_admin_cannot_approve_a_booking_that_conflicts_with_a_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = $this->createPendingBooking();
        Schedule::create([
            'lab_id' => $booking->lab_id,
            'day' => 'Senin',
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-12',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'course' => 'Kelas yang Sudah Ada',
            'lecturer' => 'Dosen Uji',
            'type' => 'perkuliahan_tetap',
            'student_count' => 30,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.booking.approve', $booking))
            ->assertRedirect(route('admin.lab.bookings', ['status' => 'pending']))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
            'handled_by' => null,
        ]);
        $this->assertDatabaseMissing('schedules', ['booking_id' => $booking->id]);
    }

    public function test_admin_rejection_records_the_reason_without_creating_a_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = $this->createPendingBooking();

        $this->actingAs($admin)
            ->post(route('admin.booking.reject', $booking), [
                'rejection_reason' => 'Dokumen belum lengkap.',
                'return_status' => 'pending',
            ])
            ->assertRedirect(route('admin.lab.bookings', ['status' => 'pending']));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
            'rejection_reason' => 'Dokumen belum lengkap.',
            'handled_by' => $admin->id,
        ]);
        $this->assertDatabaseMissing('schedules', ['booking_id' => $booking->id]);
    }

    private function createPendingBooking(): Booking
    {
        $lab = Lab::create([
            'name' => 'Lab Persetujuan',
            'capacity' => 40,
            'status' => 'available',
        ]);

        return Booking::create([
            'lab_id' => $lab->id,
            'booking_type' => 'perkuliahan_tidak_tetap',
            'unit_type' => 's1_tembalang',
            'pic_name' => 'Mahasiswa Uji',
            'study_program' => 'Manajemen',
            'nim' => '12345678901234',
            'phone_number' => '081234567890',
            'course_name' => 'Praktik Akuntansi',
            'lecturer_name' => 'Dosen Pengampu',
            'lecturer_nip' => '123456789012345678',
            'day' => 'Senin',
            'booking_date' => '2026-10-12',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'is_recurring' => false,
            'tracking_token' => str_repeat('a', 32),
        ]);
    }
}
