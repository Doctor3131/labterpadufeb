<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Lab;
use App\Models\Schedule;
use App\Models\ScheduleOccurrence;
use App\Models\User;
use App\Services\ScheduleCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-29 10:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_cancel_a_single_occurrence_and_keep_the_rest_of_the_schedule(): void
    {
        [$admin, $schedule] = $this->schedule();

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'single',
            'occurrence_date' => '2026-09-30',
            'change_reason' => 'Kegiatan hari Rabu dibatalkan',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success', 'Pertemuan tanggal 30/09/2026 dari jadwal "Pelatihan Tendik (Senin)" berhasil dibatalkan!');

        $this->assertDatabaseHas('schedule_occurrences', [
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-30 00:00:00',
            'type' => ScheduleOccurrence::TYPE_CANCELLED,
        ]);
        $this->assertSame('2026-10-03', $schedule->fresh()->end_date->toDateString());

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-29'),
            Carbon::parse('2026-10-01')
        );

        $this->assertSame(['2026-09-29', '2026-10-01'], $events->pluck('date')->all());
    }

    public function test_admin_can_cancel_from_a_selected_date_and_preserve_past_history(): void
    {
        [$admin, $schedule] = $this->schedule();

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'future',
            'occurrence_date' => '2026-09-30',
            'change_reason' => 'Rangkaian pelatihan dihentikan',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success', 'Jadwal "Pelatihan Tendik (Senin)" dibatalkan mulai tanggal 30/09/2026 dan seterusnya!');

        $this->assertSame('2026-09-29', $schedule->fresh()->end_date->toDateString());
        $this->assertDatabaseHas('schedule_change_logs', [
            'schedule_id' => $schedule->id,
            'action' => 'cancel',
            'scope' => 'future',
            'effective_date' => '2026-09-30 00:00:00',
        ]);

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-28'),
            Carbon::parse('2026-10-03')
        );

        $this->assertSame(['2026-09-28', '2026-09-29'], $events->pluck('date')->all());
    }

    public function test_cancel_all_uses_the_next_occurrence_and_marks_the_booking_deleted(): void
    {
        [$admin, $schedule] = $this->schedule(withBooking: true);

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'all',
            'change_reason' => 'Pengajuan dibatalkan',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success');

        $this->assertSame('2026-09-28', $schedule->fresh()->end_date->toDateString());
        $this->assertDatabaseHas('bookings', [
            'id' => $schedule->booking_id,
            'status' => 'deleted',
            'handled_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('schedule_change_logs', [
            'schedule_id' => $schedule->id,
            'action' => 'cancel',
            'scope' => 'future',
            'effective_date' => '2026-09-29 00:00:00',
        ]);
    }

    public function test_cancelling_a_one_time_schedule_keeps_its_record_and_hides_its_calendar_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Pembatalan', 'capacity' => 40, 'status' => 'available']);
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Rabu',
            'start_date' => '2026-09-30',
            'end_date' => '2026-09-30',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Ujian Sekali',
            'type' => 'perkuliahan_tidak_tetap',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'all',
            'change_reason' => 'Ujian dijadwalkan ulang',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);
        $this->assertDatabaseHas('schedule_occurrences', [
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-30 00:00:00',
            'type' => ScheduleOccurrence::TYPE_CANCELLED,
        ]);
        $this->assertCount(0, app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-30'),
            Carbon::parse('2026-09-30')
        ));
    }

    public function test_cancelling_a_moved_occurrence_uses_its_future_calendar_date(): void
    {
        [$admin, $schedule, $lab] = $this->weekdaySchedule();
        $this->movedOccurrence($schedule, $lab, '2026-09-28', '2026-10-01');

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'single',
            'occurrence_date' => '2026-09-28',
            'effective_date' => '2026-10-01',
            'change_reason' => 'Pertemuan pindahan dibatalkan',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success', 'Pertemuan tanggal 01/10/2026 dari jadwal "Kuliah Senin (Senin)" berhasil dibatalkan!');

        $this->assertDatabaseHas('schedule_occurrences', [
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-28 00:00:00',
            'type' => ScheduleOccurrence::TYPE_CANCELLED,
            'override_date' => null,
        ]);

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-28'),
            Carbon::parse('2026-10-12')
        );
        $this->assertSame(['2026-10-05', '2026-10-12'], $events->pluck('date')->all());
    }

    public function test_admin_can_cancel_a_moved_occurrence_without_submitting_its_effective_date(): void
    {
        [$admin, $schedule, $lab] = $this->weekdaySchedule();
        $this->movedOccurrence($schedule, $lab, '2026-09-28', '2026-10-01');

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'single',
            'occurrence_date' => '2026-09-28',
            'change_reason' => 'Dibatalkan tanpa tanggal efektif',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success', 'Pertemuan tanggal 01/10/2026 dari jadwal "Kuliah Senin (Senin)" berhasil dibatalkan!');
        $this->assertDatabaseHas('schedule_occurrences', [
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-28 00:00:00',
            'type' => ScheduleOccurrence::TYPE_CANCELLED,
            'override_date' => null,
        ]);
    }

    public function test_calendar_drag_cancel_can_cancel_a_moved_occurrence_after_its_source_date_passes(): void
    {
        [$admin, $schedule, $lab] = $this->weekdaySchedule();
        $this->movedOccurrence($schedule, $lab, '2026-09-28', '2026-10-01');

        $response = $this->actingAs($admin)->postJson(route('admin.schedules.calendar.change', $schedule), [
            'action' => 'cancel',
            'scope' => 'single',
            'occurrence_date' => '2026-09-28',
            'reason' => 'Dibatalkan dari kalender',
        ]);

        $response->assertOk()->assertJson(['message' => 'Jadwal berhasil dibatalkan tanpa menghapus histori.']);
        $this->assertDatabaseHas('schedule_occurrences', [
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-28 00:00:00',
            'type' => ScheduleOccurrence::TYPE_CANCELLED,
            'override_date' => null,
        ]);
    }

    public function test_cancelling_forward_from_a_moved_occurrence_clears_all_later_moved_events(): void
    {
        [$admin, $schedule, $lab] = $this->weekdaySchedule();
        $this->movedOccurrence($schedule, $lab, '2026-09-28', '2026-10-01');

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'future',
            'occurrence_date' => '2026-09-28',
            'effective_date' => '2026-10-01',
            'change_reason' => 'Rangkaian dihentikan',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHas('success', 'Jadwal "Kuliah Senin (Senin)" dibatalkan mulai tanggal 01/10/2026 dan seterusnya!');

        $this->assertSame('2026-09-30', $schedule->fresh()->end_date->toDateString());
        $this->assertDatabaseHas('schedule_occurrences', [
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-28 00:00:00',
            'type' => ScheduleOccurrence::TYPE_CANCELLED,
            'override_date' => null,
        ]);
        $this->assertTrue(app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-12')
        )->isEmpty());
    }

    public function test_cancel_all_includes_moved_events_that_precede_the_next_regular_occurrence(): void
    {
        [$admin, $schedule] = $this->schedule(withBooking: true);
        $lab = $schedule->lab;
        $this->movedOccurrence($schedule, $lab, '2026-09-28', '2026-10-01');

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule), [
            'scope' => 'all',
            'change_reason' => 'Pengajuan dibatalkan seluruhnya',
        ]);

        $response->assertRedirect(route('admin.schedules.index'))->assertSessionHas('success');
        $this->assertSame('2026-09-28', $schedule->fresh()->end_date->toDateString());
        $this->assertDatabaseHas('schedule_occurrences', [
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-28 00:00:00',
            'type' => ScheduleOccurrence::TYPE_CANCELLED,
            'override_date' => null,
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $schedule->booking_id,
            'status' => 'deleted',
            'handled_by' => $admin->id,
        ]);
        $this->assertTrue(app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-29'),
            Carbon::parse('2026-10-03')
        )->isEmpty());
    }

    public function test_single_and_future_cancellation_require_an_explicit_date(): void
    {
        foreach (['single', 'future'] as $scope) {
            [$admin, $schedule] = $this->schedule();

            $response = $this->actingAs($admin)->from(route('admin.schedules.index'))
                ->delete(route('admin.schedules.destroy', $schedule), [
                    'scope' => $scope,
                    'change_reason' => 'Tanggal tidak disertakan',
                ]);

            $response->assertRedirect(route('admin.schedules.index'))
                ->assertSessionHasErrors([
                    'occurrence_date' => 'Pilih tanggal pertemuan yang akan dibatalkan.',
                ]);
            $this->assertSame('2026-10-03', $schedule->fresh()->end_date->toDateString());
            $this->assertDatabaseMissing('schedule_occurrences', ['schedule_id' => $schedule->id]);
        }
    }

    public function test_one_time_schedule_rejects_single_or_future_cancellation_scopes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Pembatalan', 'capacity' => 40, 'status' => 'available']);
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Rabu',
            'start_date' => '2026-09-30',
            'end_date' => '2026-09-30',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Ujian Sekali',
            'type' => 'perkuliahan_tidak_tetap',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.schedules.index'))
            ->delete(route('admin.schedules.destroy', $schedule), [
                'scope' => 'future',
                'occurrence_date' => '2026-09-30',
                'effective_date' => '2026-09-30',
                'change_reason' => 'Gunakan scope yang sesuai',
            ]);

        $response->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHasErrors(['scope' => 'Untuk jadwal sekali jalan, pilih pembatalan seluruh jadwal.']);
        $this->assertSame('2026-09-30', $schedule->fresh()->end_date->toDateString());
        $this->assertDatabaseMissing('schedule_occurrences', ['schedule_id' => $schedule->id]);
    }

    public function test_schedule_cancellation_options_have_clickable_accessible_labels(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.schedules.index'))
            ->assertOk()
            ->assertSee('for="delete-scope-all-radio"', false)
            ->assertSee('for="delete-scope-single-radio"', false)
            ->assertSee('for="delete-scope-future-radio"', false)
            ->assertSee("delete-future-date').addEventListener('focus'", false);
    }

    private function schedule(bool $withBooking = false): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Pembatalan', 'capacity' => 40, 'status' => 'available']);
        $booking = null;

        if ($withBooking) {
            $booking = Booking::create([
                'lab_id' => $lab->id,
                'booking_type' => 'non_perkuliahan',
                'pic_name' => 'Panitia Tendik',
                'study_program' => 'FEB',
                'nim' => 'NIP-TEST',
                'phone_number' => '08123456789',
                'activity_type' => 'Pelatihan',
                'activity_name' => 'Pelatihan Tendik',
                'day' => 'Senin',
                'booking_date' => '2026-09-28',
                'start_time' => '07:00',
                'end_time' => '16:00',
                'participant_count' => 20,
                'tracking_token' => str_repeat('a', 32),
            ]);
        }

        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'booking_id' => $booking?->id,
            'day' => 'Senin',
            'start_date' => '2026-09-28',
            'end_date' => '2026-10-03',
            'start_time' => '07:00',
            'end_time' => '16:00',
            'course' => 'Pelatihan Tendik',
            'type' => 'non_perkuliahan',
            'student_count' => 20,
        ]);

        return [$admin, $schedule];
    }

    private function weekdaySchedule(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Pembatalan', 'capacity' => 40, 'status' => 'available']);
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'recurrence_days' => ['Senin'],
            'start_date' => '2026-09-28',
            'end_date' => '2026-10-12',
            'start_time' => '07:00',
            'end_time' => '09:00',
            'course' => 'Kuliah Senin',
            'type' => 'perkuliahan_tetap',
        ]);

        return [$admin, $schedule, $lab];
    }

    private function movedOccurrence(Schedule $schedule, Lab $lab, string $originalDate, string $targetDate): ScheduleOccurrence
    {
        return ScheduleOccurrence::create([
            'schedule_id' => $schedule->id,
            'occurrence_date' => $originalDate,
            'override_date' => $targetDate,
            'type' => ScheduleOccurrence::TYPE_MOVED,
            'lab_id' => $lab->id,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'change_reason' => 'Pemindahan sebelumnya',
            'changed_by' => null,
        ]);
    }
}
