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
        Schema::create('asset_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('lab_id')->constrained('labs')->cascadeOnDelete();
            
            // Komponen pembentuk asset_tag (untuk STRUCTURED_TAG)
            // Format: {proc_source}.{arrival_mmyy}.{type_code}.{lab_code}.{seq atau ADMIN}
            // Contoh: 01.1023.O1.EL305.001 atau 01.1023.H3.EL305.ADMIN
            $table->string('proc_source_code', 10)->nullable();
            $table->string('arrival_mmyy', 4)->nullable();
            $table->string('type_code', 10)->nullable();
            $table->string('lab_code_snapshot', 20)->nullable(); // Snapshot dari lab.code saat pembuatan (tanpa titik)
            $table->unsignedInteger('seq_number')->nullable(); // NULL untuk ADMIN
            
            // Hasil gabungan atau identifier untuk SEAT_NUMBER
            $table->string('asset_tag')->unique();
            
            // Subtype untuk variasi (e.g., ADMIN untuk PC AIO)
            $table->string('subtype', 50)->nullable();
            
            // Status kondisi
            $table->enum('condition', ['BAIK', 'RUSAK', 'HILANG', 'MAINTENANCE'])->default('BAIK');
            
            // Future: untuk borrowing/availability
            $table->boolean('is_available')->default(true);
            
            $table->timestamps();
            
            // Index untuk pencarian cepat
            $table->index(['lab_id', 'condition']);
            $table->index(['batch_id', 'lab_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_units');
    }
};
