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
        Schema::create('bloomberg_requests', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();

            // Personal Data
            $table->string('name');
            $table->string('nim_nip'); // NIM for students, NIP for lecturers
            $table->string('phone'); // No. HP

            // Applicant Type
            $table->enum('applicant_type', ['mahasiswa', 'dosen_undip', 'dosen_non_undip']);
            $table->string('study_program')->nullable(); // Only for students

            // Schedule
            $table->date('usage_date');
            $table->enum('session', ['sesi_1', 'sesi_2']);
            // sesi_1: 09.00 - 12.00
            // sesi_2: 13.00 - 15.00 / 13.30 - 15.00 (Jumat)

            // Purpose
            $table->enum('purpose', [
                'skripsi', 'thesis', 'disertasi',
                'sertifikasi_bloomberg', 'lomba',
                'tugas_mk', 'penelitian_dosen',
                'explore', 'lainnya'
            ]);
            $table->string('research_title')->nullable(); // Judul Penelitian / Nama Lomba
            $table->string('subject_name')->nullable(); // Mata kuliah (for tugas_mk)
            $table->string('lecturer_name')->nullable(); // Nama dosen (for penelitian_dosen)

            // Documents
            $table->string('statement_file'); // Surat Pengantar Kaprodi/Dosen

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
        Schema::dropIfExists('bloomberg_requests');
    }
};
