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
        Schema::table('asset_borrowings', function (Blueprint $table) {
            // Nomor Surat Peminjaman
            $table->string('document_number')->nullable()->after('id'); // Format: 003/SPB/UPKFEB/IX/2025
            
            // PIHAK PERTAMA (Admin/Penanggung Jawab)
            $table->string('first_party_name')->nullable()->after('document_number');
            $table->string('first_party_position')->nullable(); // Jabatan (e.g., Asisten UPK)
            $table->string('first_party_address')->nullable();
            $table->string('first_party_phone')->nullable();
            
            // PIHAK KEDUA (Peminjam) - sudah ada di borrower_name, phone_number, dll
            // Hanya perlu menambahkan alamat
            $table->string('borrower_address')->nullable()->after('email');
            
            // Tanggal Surat
            $table->date('document_date')->nullable()->after('first_party_phone');
            
            // PDF yang sudah digenerate
            $table->string('generated_document_path')->nullable()->after('document_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_borrowings', function (Blueprint $table) {
            $table->dropColumn([
                'document_number',
                'first_party_name',
                'first_party_position',
                'first_party_address',
                'first_party_phone',
                'borrower_address',
                'document_date',
                'generated_document_path'
            ]);
        });
    }
};
