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
        Schema::table('asset_borrowing_items', function (Blueprint $table) {
            // Kondisi barang saat pengembalian (per unit)
            $table->string('return_condition')->nullable()->after('remarks'); // BAIK, RUSAK_RINGAN, RUSAK_BERAT, HILANG
            $table->text('return_notes')->nullable()->after('return_condition'); // Catatan kerusakan per unit
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_borrowing_items', function (Blueprint $table) {
            $table->dropColumn(['return_condition', 'return_notes']);
        });
    }
};
