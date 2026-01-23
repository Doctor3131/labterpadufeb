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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama barang: Laptop Dell, PC AIO HP, dll
            $table->foreignId('asset_type_code_id')->nullable()->constrained('asset_type_codes')->nullOnDelete();
            $table->enum('tracking_mode', ['STRUCTURED_TAG', 'SEAT_NUMBER', 'AGGREGATE']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
