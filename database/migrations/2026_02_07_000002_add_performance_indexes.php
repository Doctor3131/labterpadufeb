<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds indexes to frequently queried columns for better performance
     */
    public function up(): void
    {
        // Get existing indexes for bookings table
        $bookingIndexes = collect(DB::select("SHOW INDEX FROM bookings"))->pluck('Key_name')->unique();
        
        Schema::table('bookings', function (Blueprint $table) use ($bookingIndexes) {
            if (!$bookingIndexes->contains('bookings_lab_day_status_index')) {
                $table->index(['lab_id', 'day', 'status'], 'bookings_lab_day_status_index');
            }
            if (!$bookingIndexes->contains('bookings_booking_date_index')) {
                $table->index('booking_date', 'bookings_booking_date_index');
            }
        });

        // Get existing indexes for schedules table
        $scheduleIndexes = collect(DB::select("SHOW INDEX FROM schedules"))->pluck('Key_name')->unique();

        Schema::table('schedules', function (Blueprint $table) use ($scheduleIndexes) {
            if (!$scheduleIndexes->contains('schedules_lab_day_dates_index')) {
                $table->index(['lab_id', 'day', 'start_date', 'end_date'], 'schedules_lab_day_dates_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $bookingIndexes = collect(DB::select("SHOW INDEX FROM bookings"))->pluck('Key_name')->unique();
        $scheduleIndexes = collect(DB::select("SHOW INDEX FROM schedules"))->pluck('Key_name')->unique();

        Schema::table('bookings', function (Blueprint $table) use ($bookingIndexes) {
            if ($bookingIndexes->contains('bookings_lab_day_status_index')) {
                $table->dropIndex('bookings_lab_day_status_index');
            }
            if ($bookingIndexes->contains('bookings_booking_date_index')) {
                $table->dropIndex('bookings_booking_date_index');
            }
        });

        Schema::table('schedules', function (Blueprint $table) use ($scheduleIndexes) {
            if ($scheduleIndexes->contains('schedules_lab_day_dates_index')) {
                $table->dropIndex('schedules_lab_day_dates_index');
            }
        });
    }
};
