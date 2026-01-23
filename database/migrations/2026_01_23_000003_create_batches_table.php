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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('proc_source_code', 10); // 01, 02, etc. - sumber pengadaan
            $table->string('arrival_mmyy', 4); // 1023 = Oct 2023
            $table->date('procurement_date')->nullable();
            $table->string('source_description')->nullable(); // Deskripsi sumber pengadaan
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->timestamps();
            
            // Unique constraint: satu item hanya bisa punya satu batch dengan kombinasi yang sama
            $table->unique(['item_id', 'proc_source_code', 'arrival_mmyy'], 'batches_unique_procurement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
