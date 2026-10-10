<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Lab;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingRecurrenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_fixed_booking_stores_selected_weekdays_and_total_meetings(): void
    {
        Storage::fake('public');
        $lab = Lab::create([
            'name' => 'Lab A',
            'capacity' => 40,
            'status' => 'available',
        ]);

        $response = $this->post(route('booking.store'), [
            'booking_type' => 'perkuliahan_tidak_tetap',
            'schedule_frequency' => 'multiple',
            'recurrence_days' => ['Senin', 'Rabu'],
            'repeat_count' => 4,
            'unit_type' => 's1_tembalang',
            'pic_name' => 'Mahasiswa Pengaju',
            'study_program' => 'Manajemen',
            'nim' => '12345678901234',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'booking_date' => '2026-09-07',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'course_name' => 'Praktik Akuntansi',
            'lecturer_name' => 'Dosen Pengampu',
            'lecturer_nip' => '123456789012345678',
            'document' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
        ]);

        $booking = Booking::firstOrFail();

        $response->assertRedirect(route('booking.success', $booking->tracking_token));
        $this->assertTrue($booking->is_recurring);
        $this->assertSame(['Senin', 'Rabu'], $booking->recurrence_days);
        $this->assertSame('2026-09-16', $booking->end_date->toDateString());
    }

    public function test_public_booking_can_be_tracked_with_its_opaque_token_without_sign_in(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 00:00:00'));
        Storage::fake('public');
        $lab = Lab::create([
            'name' => 'Lab A',
            'capacity' => 40,
            'status' => 'available',
        ]);

        $response = $this->post(route('booking.store'), [
            'booking_type' => 'perkuliahan_tidak_tetap',
            'schedule_frequency' => 'once',
            'unit_type' => 's1_tembalang',
            'pic_name' => 'Mahasiswa Pengaju',
            'study_program' => 'Manajemen',
            'nim' => '12345678901234',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'booking_date' => '2026-09-07',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'course_name' => 'Praktik Akuntansi',
            'lecturer_name' => 'Dosen Pengampu',
            'lecturer_nip' => '123456789012345678',
            'document' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
        ]);

        $booking = Booking::firstOrFail();

        $response->assertRedirect(route('booking.success', $booking->tracking_token));
        $this->assertSame('pending', $booking->status);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $booking->tracking_token);

        $this->get(route('booking.success', $booking->tracking_token))
            ->assertOk()
            ->assertSee('Praktik Akuntansi');
        $this->get(route('booking.success', $booking->id))->assertNotFound();
    }

    public function test_booking_submission_rejects_an_overlapping_pending_request(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 00:00:00'));
        Storage::fake('public');
        $lab = Lab::create([
            'name' => 'Lab A',
            'capacity' => 40,
            'status' => 'available',
        ]);
        Booking::create([
            'lab_id' => $lab->id,
            'booking_type' => 'perkuliahan_tidak_tetap',
            'unit_type' => 's1_tembalang',
            'pic_name' => 'Pengaju Sebelumnya',
            'study_program' => 'Manajemen',
            'nim' => '12345678901234',
            'phone_number' => '081234567890',
            'day' => 'Senin',
            'booking_date' => '2026-09-07',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'tracking_token' => str_repeat('a', 32),
        ]);

        $response = $this->from(route('booking.create'))->post(route('booking.store'), [
            'booking_type' => 'perkuliahan_tidak_tetap',
            'schedule_frequency' => 'once',
            'unit_type' => 's1_tembalang',
            'pic_name' => 'Pengaju Baru',
            'study_program' => 'Manajemen',
            'nim' => '12345678901234',
            'phone_number' => '081234567890',
            'lab_id' => $lab->id,
            'booking_date' => '2026-09-07',
            'start_time' => '08:30',
            'end_time' => '09:30',
            'participant_count' => 20,
            'course_name' => 'Praktik Akuntansi',
            'lecturer_name' => 'Dosen Pengampu',
            'lecturer_nip' => '123456789012345678',
            'document' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
        ]);

        $response->assertRedirect(route('booking.create'));
        $response->assertSessionHasErrors('time_conflict');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_lecturer_involvement_options_are_mutually_exclusive_on_the_server(): void
    {
        $response = $this->from(route('booking.create'))->post(route('booking.store'), [
            'booking_type' => 'non_perkuliahan',
            'is_on_behalf_lecturer' => '1',
            'is_bimbingan_dosen' => '1',
        ]);

        $response->assertRedirect(route('booking.create'));
        $response->assertSessionHasErrors('lecturer_involvement');
        $this->assertDatabaseCount('bookings', 0);
    }
}
