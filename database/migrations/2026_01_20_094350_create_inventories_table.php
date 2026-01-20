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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique(); // ID Barang
            $table->string('name'); // Nama Barang
            $table->integer('quantity'); // Jumlah Barang
            $table->string('condition'); // Kondisi: Baik, Rusak, Perbaikan
            $table->decimal('price', 15, 2); // Harga
            $table->string('source'); // Sumber Alat
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
