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
            $table->foreignId('lab_id')->constrained()->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->date('start_date')->nullable(); // Tanggal mulai semester/periode
            $table->date('end_date')->nullable(); // Tanggal akhir semester/periode
            $table->time('start_time');
            $table->time('end_time');
            $table->string('course'); // Mata Kuliah
            $table->string('lecturer'); // Dosen
            $table->enum('type', ['regular', 'booking_recurring', 'booking_onetime'])->default('regular');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
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
