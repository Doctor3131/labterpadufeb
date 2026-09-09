<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings MODIFY lab_id BIGINT UNSIGNED NULL');

            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before reverting, ensure no NULL lab_id exists
        // This will fail if there are personal bookings without lab_id
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings MODIFY lab_id BIGINT UNSIGNED NOT NULL');

            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_id')->nullable(false)->change();
        });
    }
};
