<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Models\Schedule;
use App\Models\User;
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
}
