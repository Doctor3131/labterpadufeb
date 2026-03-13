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
        Schema::create('asset_type_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // H3, O1, I2, BRK, J1, L1, P, etc.
            $table->string('name'); // PC AIO, Laptop, TV, Bracket, Speaker, Printer, Samsung Tab
            $table->enum('default_tracking_mode', ['STRUCTURED_TAG', 'SEAT_NUMBER', 'AGGREGATE'])
                  ->default('STRUCTURED_TAG');
            $table->boolean('is_borrowable')->default(true); // Future: untuk fitur peminjaman
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_type_codes');
    }
};
