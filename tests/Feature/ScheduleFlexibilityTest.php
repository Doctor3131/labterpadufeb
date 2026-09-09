<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Models\Schedule;
use App\Models\ScheduleChangeLog;
use App\Services\ScheduleCalendarService;
use App\Services\ScheduleChangeService;
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
