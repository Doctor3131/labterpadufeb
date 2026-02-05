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
        Schema::create('refinitiv_requests', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            
            // Personal Data
            $table->string('name');
            $table->string('nim_nip'); // NIM for students, NIP for lecturers
            $table->string('whatsapp');
            
            // Affiliation
            $table->enum('affiliation', ['internal_feb', 'internal_undip', 'eksternal']); // Keterangan
            
            // Applicant Type - determines if lecturer or student
            $table->enum('applicant_type', ['dosen', 'mahasiswa']);
            $table->string('study_program')->nullable(); // Only for students
            
            // Purpose
            $table->enum('purpose', ['skripsi', 'thesis', 'disertasi', 'lomba', 'tugas_mk', 'penelitian_dosen', 'lainnya']);
            $table->string('purpose_other')->nullable(); // If "lainnya" is selected
            $table->string('lecturer_name')->nullable(); // For penelitian_dosen
            
            // Schedule
            $table->date('usage_date');
            $table->enum('session', ['sesi_1', 'sesi_2', 'sesi_3']);
            // sesi_1: 08.00 - 10.00
            // sesi_2: 10.00 - 12.00
            // sesi_3: 13.00 - 15.00 / 13.30 - 15.30 (Jumat)
            
            // Data Requirements
            $table->text('variables'); // Variabel yang dibutuhkan
            
            // Documents
            $table->string('ktm_file')->nullable(); // Only for students
            $table->string('statement_file'); // Surat Pernyataan Kesanggupan
            
            // Status - for attendance tracking
            $table->enum('attendance_status', ['pending', 'hadir', 'tidak_hadir'])->default('pending');
            $table->timestamp('attendance_marked_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Admin notes
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('usage_date');
            $table->index('attendance_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refinitiv_requests');
    }
};
