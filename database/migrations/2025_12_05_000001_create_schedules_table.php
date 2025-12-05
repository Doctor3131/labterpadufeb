<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->onDelete('cascade'); // Relasi ke labs
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('course'); // Mata Kuliah
            $table->string('lecturer'); // Dosen
            $table->string('komting')->nullable(); // PIC Mahasiswa
            $table->string('phone')->nullable(); // No HP
            $table->integer('student_count')->nullable(); // Jumlah Mahasiswa
            $table->enum('type', ['regular', 'booking'])->default('regular'); // regular = jadwal tetap, booking = dari peminjaman
            $table->unsignedBigInteger('booking_id')->nullable(); // Link ke bookings jika dari peminjaman
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
