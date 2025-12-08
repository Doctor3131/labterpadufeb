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
            
            // Tipe peminjaman
            $table->enum('booking_type', ['perkuliahan_tetap', 'perkuliahan_tidak_tetap', 'non_perkuliahan']);
            
            // Data Peminjam (untuk non-perkuliahan)
            $table->string('nama_peminjam');
            $table->string('program_studi');
            $table->string('nim');
            $table->string('no_telpon');
            $table->string('alamat')->nullable(); // Alamat tempat tinggal
            
            // Detail Peminjaman Non-Perkuliahan
            $table->enum('jenis_kegiatan', [
                'Seminar',
                'Workshop',
                'Pelatihan',
                'Rapat',
                'Ujian',
                'Lainnya'
            ])->nullable();
            $table->string('jabatan')->nullable();
            $table->text('kebutuhan_peralatan')->nullable();
            $table->string('nama_kegiatan')->nullable();
            
            // Detail Perkuliahan
            $table->string('mata_kuliah')->nullable(); // untuk perkuliahan
            $table->string('dosen_pengampu')->nullable(); // Nama dosen/instruktur
            $table->string('nip_dosen')->nullable();
            $table->string('software_digunakan')->nullable();
            $table->boolean('is_recurring')->default(false); // true untuk perkuliahan tetap
            
            // Jadwal
            $table->date('tanggal'); // Tanggal peminjaman
            $table->time('start_time');
            $table->time('end_time');
            
            // Peserta & Dokumen
            $table->integer('jumlah_peserta');
            $table->string('document_path')->nullable(); // PDF gabungan
            
            // Status & Approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
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
