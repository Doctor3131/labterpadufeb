<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CONSOLIDATED MIGRATION - includes:
     * - Original schedules table structure
     * - komting and student_count columns (from 2025_12_22)
     * - Final type ENUM without 'regular' (consolidated from 2025_12_31 and 2026_01_08)
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->date('start_date')->nullable(); // Tanggal mulai semester/periode
            $table->date('end_date')->nullable(); // Tanggal akhir semester/periode
            $table->time('start_time');
            $table->time('end_time');
            $table->string('course')->nullable(); // Mata Kuliah
            $table->string('lecturer')->nullable(); // Dosen
            
            // Final type ENUM - 'regular' merged into 'perkuliahan_tetap'
            $table->enum('type', [
                'perkuliahan_tetap',
                'perkuliahan_tidak_tetap',
                'non_perkuliahan',
                'pribadi'
            ])->default('perkuliahan_tetap');
            
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
            $table->string('komting')->nullable(); // Koordinator/Komting
            $table->integer('student_count')->nullable(); // Jumlah mahasiswa
            $table->timestamps();
            
            // Indexes untuk performance
            $table->index(['lab_id', 'day', 'start_time']);
            $table->index('booking_id');
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
