<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Lab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPublicContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_print_view_requires_the_opaque_tracking_token(): void
    {
        $booking = $this->createBooking();

        $this->get(route('booking.print', $booking->tracking_token))
            ->assertOk()
            ->assertSee('Praktik Akuntansi');

        $this->get(route('booking.print', $booking->id))->assertNotFound();
    }

    public function test_personal_booking_does_not_expose_a_print_form(): void
    {
        $booking = $this->createBooking([
            'booking_type' => 'pribadi',
            'pribadi_sub_type' => 'mahasiswa',
            'course_name' => null,
        ]);

        $this->get(route('booking.print', $booking->tracking_token))->assertNotFound();
    }

    public function test_available_labs_excludes_rooms_below_the_requested_capacity(): void
    {
        $smallLab = Lab::create(['name' => 'Lab Kecil', 'capacity' => 10, 'status' => 'available']);
        $largeLab = Lab::create(['name' => 'Lab Besar', 'capacity' => 40, 'status' => 'available']);

        $this->postJson(route('booking.available-labs'), [
            'booking_type' => 'perkuliahan_tidak_tetap',
            'schedule_frequency' => 'once',
            'participant_count' => 20,
            'booking_date' => '2026-10-12',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ])
            ->assertOk()
            ->assertJsonMissing(['id' => $smallLab->id])
            ->assertJsonFragment(['id' => $largeLab->id]);
    }

    public function test_available_labs_rejects_malformed_dates_and_time_ranges(): void
    {
        Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);

        $this->postJson(route('booking.available-labs'), [
            'participant_count' => 20,
            'booking_date' => 'not-a-date',
            'start_time' => '10:00',
            'end_time' => '08:00',
        ])->assertUnprocessable();
    }

    public function test_calendar_availability_rejects_ranges_longer_than_sixty_three_days(): void
    {
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);

        $this->getJson(route('booking.calendar-availability', [
            'start' => '2026-10-01',
            'end' => '2027-01-01',
            'lab_id' => $lab->id,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end');
    }

    private function createBooking(array $overrides = []): Booking
    {
        $lab = Lab::create([
            'name' => 'Lab Cetak',
            'capacity' => 40,
            'status' => 'available',
        ]);

        return Booking::create(array_merge([
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
            'tracking_token' => str_repeat('c', 32),
        ], $overrides));
    }
}
