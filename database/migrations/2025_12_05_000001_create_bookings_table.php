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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->onDelete('cascade');
            
            // Booking Type
            $table->enum('booking_type', ['perkuliahan_tetap', 'perkuliahan_tidak_tetap', 'non_perkuliahan']);
            
            // Borrower Data (for non-perkuliahan & general PIC)
            $table->string('pic_name'); // Nama Peminjam
            $table->string('study_program'); // Program Studi
            $table->string('nim');
            $table->string('phone_number'); // No Telpon
            $table->text('address')->nullable(); // Alamat tempat tinggal
            
            // Non-Lecture Booking Details
            $table->enum('activity_type', [ // Jenis Kegiatan
                'Seminar',
                'Workshop',
                'Pelatihan',
                'Rapat',
                'Ujian',
                'Lainnya'
            ])->nullable();
            $table->string('position')->nullable(); // Jabatan
            $table->text('equipment_needs')->nullable(); // Kebutuhan Peralatan
            $table->string('activity_name')->nullable(); // Nama Kegiatan
            
            // Lecture Details
            $table->string('course_name')->nullable(); // Mata Kuliah
            $table->string('lecturer_name')->nullable(); // Dosen Pengampu
            $table->string('lecturer_nip', 18)->nullable(); // NIP Dosen
            $table->string('software_needs')->nullable(); // Software Digunakan
            $table->boolean('is_recurring')->default(false);
            
            // Schedule Info
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->date('booking_date'); // Tanggal
            $table->time('start_time');
            $table->time('end_time');
            
            // Participants & Documents
            $table->integer('participant_count'); // Jumlah Peserta
            $table->string('document_path')->nullable();
            
            // Status & Approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('tracking_token', 32)->unique();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('nim');
            $table->index('booking_date');
            $table->index('tracking_token');
            $table->index(['lab_id', 'booking_date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
