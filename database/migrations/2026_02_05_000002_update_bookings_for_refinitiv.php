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
     * Updates bookings table to support Refinitiv-specific features:
     * - borrower_type: mahasiswa/dosen
     * - attendance_status: hadir/tidak_hadir (for Refinitiv)
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add borrower type for distinguishing mahasiswa/dosen in pribadi bookings
            $table->enum('borrower_type', ['mahasiswa', 'dosen'])->nullable()->after('booking_type');
            
            // Add attendance status for Refinitiv (pribadi) bookings
            $table->enum('attendance_status', ['pending', 'hadir', 'tidak_hadir'])->nullable()->after('status');
            $table->timestamp('attendance_marked_at')->nullable()->after('attendance_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['borrower_type', 'attendance_status', 'attendance_marked_at']);
        });
    }
};
