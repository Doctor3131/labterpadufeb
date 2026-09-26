<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Models\Schedule;
use App\Models\ScheduleChangeLog;
use App\Models\ScheduleOccurrence;
use App\Services\ScheduleCalendarService;
use App\Services\ScheduleChangeService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ScheduleFlexibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-08 09:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_single_occurrence_can_move_to_another_day_without_changing_the_series(): void
    {
        [$sourceLab, $targetLab] = $this->labs();
        $schedule = $this->schedule($sourceLab);

        app(ScheduleChangeService::class)->moveOccurrence(
            $schedule,
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-15'),
            $targetLab->id,
            '10:00',
            '12:00',
            'Pemindahan ruang',
            null
        );

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-15')
        );

        $moved = $events->firstWhere('occurrence_date', '2026-09-14');
        $this->assertSame('2026-09-15', $moved['date']);
        $this->assertSame($targetLab->id, $moved['lab_id']);
        $this->assertSame('10:00', $moved['start_time']);
        $this->assertDatabaseHas('schedule_change_logs', [
            'schedule_id' => $schedule->id,
            'action' => 'move',
            'scope' => 'single',
        ]);
    }

    public function test_past_occurrence_is_immutable(): void
    {
        [$sourceLab, $targetLab] = $this->labs();
        $schedule = $this->schedule($sourceLab);

        $this->expectException(ValidationException::class);

        app(ScheduleChangeService::class)->moveOccurrence(
            $schedule,
            Carbon::parse('2026-09-07'),
            Carbon::parse('2026-09-09'),
            $targetLab->id,
            '10:00',
            '12:00',
            'Koreksi tanpa audit khusus',
            null
        );
    }

    public function test_non_perkuliahan_date_range_appears_on_each_operating_day(): void
    {
        [$lab] = $this->labs();
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'start_date' => '2026-09-14',
            'end_date' => '2026-09-17',
            'start_time' => '07:00',
            'end_time' => '16:00',
            'course' => 'Pelatihan Tendik FEB',
            'type' => 'non_perkuliahan',
            'student_count' => 20,
        ]);

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-17')
        );

        $this->assertSame(
            ['2026-09-14', '2026-09-15', '2026-09-16', '2026-09-17'],
            $events->pluck('date')->all()
        );
        $this->assertTrue($events->every(fn (array $event) => $event['is_recurring']));
        $this->assertTrue(app(ScheduleCalendarService::class)->isOccurrenceDate($schedule, Carbon::parse('2026-09-16')));
    }

    public function test_non_perkuliahan_range_can_cancel_one_day_without_removing_the_rest(): void
    {
        [$lab] = $this->labs();
        $schedule = Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'start_date' => '2026-09-14',
            'end_date' => '2026-09-17',
            'start_time' => '07:00',
            'end_time' => '16:00',
            'course' => 'Pelatihan Tendik FEB',
            'type' => 'non_perkuliahan',
            'student_count' => 20,
        ]);

        app(ScheduleChangeService::class)->cancelOccurrence(
            $schedule,
            Carbon::parse('2026-09-15'),
            'Kegiatan hari ini dibatalkan',
            null
        );

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-17')
        );

        $this->assertSame(['2026-09-14', '2026-09-16', '2026-09-17'], $events->pluck('date')->all());
        $this->assertTrue(ScheduleOccurrence::query()
            ->where('schedule_id', $schedule->id)
            ->whereDate('occurrence_date', '2026-09-15')
            ->where('type', 'cancelled')
            ->exists());
    }

    public function test_non_perkuliahan_range_checks_conflicts_on_each_operating_day(): void
    {
        [$lab] = $this->labs();
        Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Selasa',
            'recurrence_days' => ['Selasa'],
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Jadwal hari Selasa',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 20,
        ]);

        $conflict = ScheduleService::checkConflict(
            $lab->id,
            'Senin',
            '08:00',
            '10:00',
            '2026-09-14',
            '2026-09-17',
            null,
            null,
            'non_perkuliahan'
        );

        $this->assertSame('Jadwal hari Selasa pada 15/09/2026', $conflict);
    }

    public function test_moving_an_occurrence_again_updates_its_existing_override(): void
    {
        [$sourceLab, $targetLab] = $this->labs();
        $schedule = $this->schedule($sourceLab);

        $changes = app(ScheduleChangeService::class);
        $changes->moveOccurrence(
            $schedule,
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-15'),
            $targetLab->id,
            '10:00',
            '12:00',
            'Pemindahan pertama',
            null
        );
        $changes->moveOccurrence(
            $schedule,
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-16'),
            $targetLab->id,
            '11:00',
            '13:00',
            'Pemindahan kedua',
            null
        );

        $occurrence = $schedule->occurrences()->whereDate('occurrence_date', '2026-09-14')->first();

        $this->assertNotNull($occurrence);
        $this->assertSame('2026-09-16', $occurrence->override_date->toDateString());
        $this->assertSame('11:00', $occurrence->start_time);
        $this->assertSame(1, $schedule->occurrences()->count());
    }

    public function test_future_change_creates_a_new_revision_and_preserves_old_segment(): void
    {
        [$sourceLab, $targetLab] = $this->labs();
        $schedule = $this->schedule($sourceLab);

        $revision = app(ScheduleChangeService::class)->changeFuture(
            $schedule,
            Carbon::parse('2026-09-21'),
            Carbon::parse('2026-09-22'),
            [
                'lab_id' => $targetLab->id,
                'start_time' => '10:00',
                'end_time' => '12:00',
                'end_date' => '2026-10-31',
            ],
            'Perubahan jadwal semester',
            null
        );

        $schedule->refresh();
        $this->assertSame('2026-09-14', $schedule->end_date->toDateString());
        $this->assertSame($schedule->series_uuid, $revision->series_uuid);
        $this->assertSame($schedule->id, $revision->parent_schedule_id);
        $this->assertSame(2, $revision->revision_number);
        $this->assertSame('2026-09-22', $revision->start_date->toDateString());

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-22')
        );
        $this->assertNotNull($events->firstWhere('date', '2026-09-14'));
        $this->assertSame($targetLab->id, $events->firstWhere('date', '2026-09-22')['lab_id']);
        $this->assertSame(2, ScheduleChangeLog::where('series_uuid', $schedule->series_uuid)->count());
    }

    public function test_moved_occurrence_blocks_its_target_slot(): void
    {
        [$sourceLab, $targetLab] = $this->labs();
        $schedule = $this->schedule($sourceLab);

        app(ScheduleChangeService::class)->moveOccurrence(
            $schedule,
            Carbon::parse('2026-09-14'),
            Carbon::parse('2026-09-15'),
            $targetLab->id,
            '10:00',
            '12:00',
            'Pemindahan ruang',
            null
        );

        $conflict = app(ScheduleCalendarService::class)->findConflict(
            $targetLab->id,
            Carbon::parse('2026-09-15'),
            '11:00',
            '13:00'
        );

        $this->assertSame('schedule', $conflict['source']);
    }

    public function test_cancelling_future_hides_future_moved_exceptions(): void
    {
        [$sourceLab, $targetLab] = $this->labs();
        $schedule = $this->schedule($sourceLab);

        app(ScheduleChangeService::class)->moveOccurrence(
            $schedule,
            Carbon::parse('2026-09-28'),
            Carbon::parse('2026-09-29'),
            $targetLab->id,
            '10:00',
            '12:00',
            'Pemindahan sementara',
            null
        );
        app(ScheduleChangeService::class)->cancelFuture(
            $schedule,
            Carbon::parse('2026-09-21'),
            'Semester dipercepat',
            null
        );

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-21'),
            Carbon::parse('2026-09-30')
        );

        $this->assertTrue($events->isEmpty());
    }

    public function test_extending_end_date_rejects_a_conflicting_slot(): void
    {
        [$sourceLab] = $this->labs();
        $schedule = $this->schedule($sourceLab);
        $schedule->update(['end_date' => '2026-09-14']);
        Schedule::create([
            'lab_id' => $sourceLab->id,
            'day' => 'Senin',
            'start_date' => '2026-09-21',
            'end_date' => '2026-09-21',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'course' => 'Jadwal Pengganti',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 20,
        ]);

        $this->expectException(ValidationException::class);

        app(ScheduleChangeService::class)->changeEndDate(
            $schedule,
            Carbon::parse('2026-10-05'),
            'Perpanjangan semester',
            null
        );
    }

    public function test_non_fixed_schedule_can_expand_across_multiple_weekdays(): void
    {
        [$sourceLab] = $this->labs();
        $schedule = Schedule::create([
            'lab_id' => $sourceLab->id,
            'day' => 'Senin',
            'recurrence_days' => ['Senin', 'Rabu'],
            'start_date' => '2026-09-07',
            'end_date' => '2026-09-21',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Kelas Praktik',
            'type' => 'perkuliahan_tidak_tetap',
            'student_count' => 20,
        ]);

        $events = app(ScheduleCalendarService::class)->events(
            Carbon::parse('2026-09-07'),
            Carbon::parse('2026-09-21')
        );

        $this->assertCount(5, $events);
        $this->assertSame(
            ['2026-09-07', '2026-09-09', '2026-09-14', '2026-09-16', '2026-09-21'],
            $events->pluck('date')->all()
        );
        $this->assertTrue($events->every(fn (array $event) => $event['is_recurring']));
    }

    public function test_conflict_check_covers_all_selected_weekdays(): void
    {
        [$sourceLab] = $this->labs();
        Schedule::create([
            'lab_id' => $sourceLab->id,
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

        $conflict = ScheduleService::checkConflict(
            $sourceLab->id,
            'Senin',
            '08:00',
            '10:00',
            '2026-09-07',
            '2026-09-16',
            null,
            ['Senin', 'Rabu']
        );

        $this->assertSame('Jadwal Rabu pada 09/09/2026', $conflict);
    }

    public function test_one_off_non_fixed_mapping_clears_previous_recurrence_metadata(): void
    {
        $mapped = ScheduleService::mapFromRequest([
            'lab_id' => 1,
            'day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'start_date' => '2026-09-14',
            'end_date' => '2026-10-26',
            'student_count' => 20,
            'schedule_frequency' => 'once',
            'recurrence_days' => ['Senin', 'Rabu'],
            'course_name' => 'Kelas Sekali',
            'lecturer_name' => 'Dosen Uji',
        ], 'perkuliahan_tidak_tetap');

        $this->assertSame('2026-09-14', $mapped['end_date']);
        $this->assertNull($mapped['recurrence_days']);
    }

    public function test_date_less_conflict_matches_a_secondary_recurrence_day(): void
    {
        [$sourceLab] = $this->labs();
        Schedule::create([
            'lab_id' => $sourceLab->id,
            'day' => 'Rabu',
            'recurrence_days' => ['Rabu'],
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Jadwal Tetap Rabu',
            'type' => 'perkuliahan_tetap',
            'student_count' => 20,
        ]);

        $conflict = ScheduleService::checkConflict(
            $sourceLab->id,
            'Senin',
            '09:00',
            '11:00',
            null,
            null,
            null,
            ['Senin', 'Rabu']
        );

        $this->assertSame('Jadwal Tetap Rabu (08:00 - 10:00)', $conflict);
    }

    private function labs(): array
    {
        return [
            Lab::create(['name' => 'Lab A', 'capacity' => 40, 'status' => 'available']),
            Lab::create(['name' => 'Lab B', 'capacity' => 40, 'status' => 'available']),
        ];
    }

    private function schedule(Lab $lab): Schedule
    {
        return Schedule::create([
            'lab_id' => $lab->id,
            'day' => 'Senin',
            'start_date' => '2026-09-07',
            'end_date' => '2026-10-26',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course' => 'Akuntansi',
            'lecturer' => 'Dosen A',
            'type' => 'perkuliahan_tetap',
            'student_count' => 30,
        ]);
    }
}
