<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update enum to include new type values
        DB::statement("ALTER TABLE schedules MODIFY COLUMN type ENUM(
            'regular',
            'perkuliahan_tetap',
            'perkuliahan_tidak_tetap',
            'non_perkuliahan',
            'pribadi',
            'booking_recurring',
            'booking_onetime'
        ) DEFAULT 'regular'");

        // Migrate old types to new types
        DB::statement("UPDATE schedules SET type = 'perkuliahan_tetap' WHERE type = 'booking_recurring'");
        DB::statement("UPDATE schedules SET type = 'non_perkuliahan' WHERE type = 'booking_onetime'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert types back
        DB::statement("UPDATE schedules SET type = 'booking_recurring' WHERE type = 'perkuliahan_tetap'");
        DB::statement("UPDATE schedules SET type = 'booking_onetime' WHERE type IN ('perkuliahan_tidak_tetap', 'non_perkuliahan', 'pribadi')");

        // Revert enum
        DB::statement("ALTER TABLE schedules MODIFY COLUMN type ENUM(
            'regular',
            'booking_recurring',
            'booking_onetime'
        ) DEFAULT 'regular'");
    }
};
