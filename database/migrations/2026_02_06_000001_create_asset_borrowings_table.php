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
        Schema::create('asset_borrowings', function (Blueprint $table) {
            $table->id();
            
            // Informasi Peminjam
            $table->string('borrower_name');
            $table->string('borrower_type'); // Mahasiswa, Dosen, Staff, Lainnya
            $table->string('borrower_id_number')->nullable(); // NIM/NIP
            $table->string('study_program')->nullable(); // untuk mahasiswa
            $table->string('class_year', 4)->nullable(); // angkatan untuk mahasiswa
            $table->string('position')->nullable(); // jabatan untuk staff/dosen
            $table->string('phone_number');
            $table->string('email')->nullable();
            
            // Detail Peminjaman
            $table->foreignId('lab_id')->nullable()->constrained('labs')->nullOnDelete(); // Lab asal barang
            $table->text('purpose'); // Tujuan peminjaman
            $table->date('borrow_date'); // Tanggal pinjam
            $table->date('return_date'); // Tanggal rencana pengembalian
            $table->time('borrow_time')->nullable(); // Jam pinjam
            $table->time('return_time')->nullable(); // Jam kembali
            
            // Status & Approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'borrowed', 'returned', 'overdue', 'cancelled'])
                  ->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Tracking
            $table->string('tracking_token', 10)->unique(); // Token untuk tracking
            
            // Handler Info
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('handed_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handed_out_at')->nullable();
            $table->foreignId('received_back_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_back_at')->nullable();
            
            // Kondisi & Catatan
            $table->text('borrow_condition_notes')->nullable(); // Kondisi saat dipinjam
            $table->text('return_condition_notes')->nullable(); // Kondisi saat dikembalikan
            $table->boolean('is_damaged_on_return')->default(false);
            $table->text('damage_description')->nullable();
            
            // Document
            $table->string('document_path')->nullable(); // KTM/KTP/Surat
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'borrow_date']);
            $table->index('tracking_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_borrowings');
    }
};
