<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_schedule_management_with_sqlite(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);

        foreach (['Rabu', 'Senin'] as $day) {
            Schedule::create([
                'lab_id' => $lab->id,
                'day' => $day,
                'start_date' => '2026-09-01',
                'end_date' => '2026-12-31',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'course' => 'Kelas '.$day,
                'lecturer' => 'Dosen Uji',
                'type' => 'perkuliahan_tetap',
                'student_count' => 30,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.schedules.index', ['month' => '2026-09']));

        $response->assertOk();
        $response->assertSeeInOrder(['Kelas Senin', 'Kelas Rabu']);
    }

    public function test_recurring_non_fixed_schedule_edit_exposes_occurrence_scope(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'recurrence_days' => ['Senin', 'Rabu'],
            'start_date' => '2026-09-07',
            'end_date' => '2026-09-30',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Kelas Tidak Tetap',
            'lecturer' => 'Dosen Uji',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 30,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.schedules.edit', $schedule));

        $response->assertOk();
        $response->assertSee('Pertemuan ini saja');
        $response->assertSee('Pertemuan ini dan berikutnya');
    }

    public function test_available_labs_excludes_conflicts_on_secondary_recurrence_day(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $blockedLab = Lab::create(['name' => 'Lab Terisi', 'capacity' => 40, 'status' => 'available']);
        $freeLab = Lab::create(['name' => 'Lab Kosong', 'capacity' => 40, 'status' => 'available']);
        Schedule::create([
            'lab_id' => $blockedLab->id,
            'day' => 'Rabu',
            'recurrence_days' => ['Rabu'],
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Jadwal Rabu',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 20,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.schedules.available-labs'), [
            'day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'start_date' => '2026-09-07',
            'end_date' => '2026-09-16',
            'recurrence_days' => ['Senin', 'Rabu'],
        ]);

        $response->assertOk();
        $response->assertJsonMissing(['id' => $blockedLab->id]);
        $response->assertJsonFragment(['id' => $freeLab->id]);
    }

    public function test_day_filter_includes_a_secondary_recurrence_day(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Uji', 'capacity' => 40, 'status' => 'available']);
        Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'recurrence_days' => ['Senin', 'Rabu'],
            'start_date' => '2026-09-07',
            'end_date' => '2026-09-30',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Kelas Senin dan Rabu',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 20,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.schedules.index', ['day' => 'Rabu', 'period' => 'all']));

        $response->assertOk();
        $response->assertSee('Kelas Senin dan Rabu');
    }

    public function test_calendar_events_include_occurrence_context_for_editing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Kalender', 'capacity' => 40, 'status' => 'available']);
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'recurrence_days' => ['Senin', 'Rabu'],
            'start_date' => '2026-09-07',
            'end_date' => '2026-09-30',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Kelas Kalender',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 20,
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.schedules.calendar.events', [
            'start' => '2026-09-14',
            'end' => '2026-09-18',
            'lab_id' => $lab->id,
        ]));

        $response->assertOk();
        $response->assertJsonFragment([
            'schedule_id' => $schedule->id,
            'occurrence_date' => '2026-09-14',
            'recurrence_days' => ['Senin', 'Rabu'],
        ]);
    }

    public function test_calendar_future_move_updates_the_recurrence_pattern(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::create(['name' => 'Lab Kalender', 'capacity' => 40, 'status' => 'available']);
        $occurrenceDate = today()->next(Carbon::WEDNESDAY);
        $targetDate = $occurrenceDate->copy()->addDay();
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'recurrence_days' => ['Senin', 'Rabu'],
            'start_date' => today()->copy()->subMonth()->toDateString(),
            'end_date' => today()->copy()->addMonths(3)->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Kelas Kalender',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 20,
        ]);

        $response = $this->actingAs($admin)->postJson(
            route('admin.schedules.calendar.change', $schedule),
            [
                'action' => 'move',
                'scope' => 'future',
                'occurrence_date' => $occurrenceDate->toDateString(),
                'target_date' => $targetDate->toDateString(),
                'lab_id' => $lab->id,
                'start_time' => '08:00',
                'end_time' => '10:00',
                'recurrence_days' => ['Senin', 'Kamis'],
                'reason' => 'Penyesuaian hari kuliah',
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('message', 'Rangkaian masa depan berhasil diperbarui.');
        $revision = Schedule::query()
            ->where('parent_schedule_id', $schedule->id)
            ->whereDate('start_date', $targetDate->toDateString())
            ->first();

        $this->assertNotNull($revision);
        $this->assertSame(['Senin', 'Kamis'], $revision->recurrence_days);
    }
}
