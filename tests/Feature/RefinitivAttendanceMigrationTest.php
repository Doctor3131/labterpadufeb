<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RefinitivAttendanceMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_migration_recovers_a_partially_created_table(): void
    {
        Schema::dropIfExists('refinitiv_attendance_events');
        Schema::create('refinitiv_attendance_events', function ($table) {
            $table->id();
            $table->unsignedBigInteger('refinitiv_request_id');
            $table->timestamp('recorded_at');
        });

        $migration = require database_path('migrations/2026_09_23_120000_create_refinitiv_attendance_events_table.php');
        $migration->up();

        $this->assertTrue(Schema::hasIndex('refinitiv_attendance_events', 'ref_attendance_request_recorded_idx'));
    }
}
