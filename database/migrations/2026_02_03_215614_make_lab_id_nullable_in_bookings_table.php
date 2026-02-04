<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Makes lab_id nullable for 'pribadi' (personal) bookings.
     * Personal bookings don't require lab selection - lab assignment 
     * is done on-site by lab assistants.
     */
    public function up(): void
    {
        // Use raw SQL to modify column without doctrine/dbal dependency
        // This is safe because we're only changing NULL constraint, not data type
        DB::statement('ALTER TABLE bookings MODIFY lab_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before reverting, ensure no NULL lab_id exists
        // This will fail if there are personal bookings without lab_id
        DB::statement('ALTER TABLE bookings MODIFY lab_id BIGINT UNSIGNED NOT NULL');
    }
};
