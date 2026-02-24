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
     * Makes several columns nullable to support pribadi bookings
     * which don't require date/time/lab/document/participant data.
     * Also adds pribadi_sub_type column (mahasiswa/non_mahasiswa).
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add pribadi_sub_type column
            $table->string('pribadi_sub_type')->nullable()->after('booking_type');
        });

        // Make columns nullable for pribadi bookings (no date/time/lab)
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_id')->nullable()->change();
            $table->date('booking_date')->nullable()->change();
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
            $table->integer('participant_count')->nullable()->change();
            $table->string('pic_name')->nullable()->change();
            $table->string('study_program')->nullable()->change();
            $table->string('nim')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
        });

        // Handle ENUM 'day' column separately (Laravel change() doesn't handle ENUMs well)
        DB::statement("ALTER TABLE bookings MODIFY COLUMN `day` ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('pribadi_sub_type');
        });
    }
};
