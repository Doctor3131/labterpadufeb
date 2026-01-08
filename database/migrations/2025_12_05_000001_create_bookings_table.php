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
     * - Original bookings table structure
     * - custom_status column (from 2025_12_24)
     * - 'deleted' status in ENUM (from 2025_12_31)
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->onDelete('cascade');
            
            // Booking Type
            $table->enum('booking_type', ['perkuliahan_tetap', 'perkuliahan_tidak_tetap', 'non_perkuliahan', 'pribadi']);
            
            // Unit Type (S1 Tembalang or Pascasarjana Pleburan) - Nullable for 'pribadi'
            $table->enum('unit_type', ['s1_tembalang', 'pascasarjana_pleburan'])->nullable();
            
            // Borrower Data (for non-perkuliahan & general PIC)
            $table->string('pic_name'); // Nama Peminjam
            $table->string('study_program'); // Program Studi
            $table->string('nim');
            $table->string('phone_number'); // No Telpon
            $table->text('address')->nullable(); // Alamat tempat tinggal
            
            // Personal Booking Details
            $table->string('applicant_status')->nullable(); // Status (Mahasiswa/Dosen/etc)
            $table->string('custom_status')->nullable(); // Custom status when 'Lainnya' is selected
            $table->string('class_year', 4)->nullable(); // Angkatan
            $table->string('purpose')->nullable(); // Keperluan
            
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
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->date('booking_date'); // Tanggal
            $table->time('start_time');
            $table->time('end_time');
            
            // Participants & Documents
            $table->integer('participant_count'); // Jumlah Peserta
            $table->string('document_path')->nullable();
            
            // Status & Approval - includes 'deleted' status
            $table->enum('status', ['pending', 'approved', 'rejected', 'deleted'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('tracking_token', 32)->unique();
            $table->text('admin_notes')->nullable();
            
            // Assignment Tracking (who handled approve/reject)
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('handled_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('nim');
            $table->index('booking_date');
            $table->index('tracking_token');
            $table->index('handled_by');
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
