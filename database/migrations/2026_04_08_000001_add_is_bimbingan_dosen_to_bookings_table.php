<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add is_bimbingan_dosen flag to bookings table.
     * When true for non_perkuliahan bookings, lecturer_name and lecturer_nip
     * are used for the supervising lecturer's data, and the "Mengetahui"
     * signature on the print form shows the lecturer instead of the PIC.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_bimbingan_dosen')->default(false)->after('activity_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('is_bimbingan_dosen');
        });
    }
};
