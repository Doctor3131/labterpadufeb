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
            // Merk/Tipe barang
            $table->string('brand_type')->nullable()->after('quantity');
            
            // Kondisi Barang saat peminjaman
            $table->boolean('condition_good')->default(true)->after('brand_type'); // Baik
            $table->boolean('condition_adequate')->default(false)->after('condition_good'); // Cukup
            $table->boolean('condition_complete')->default(true)->after('condition_adequate'); // Lengkap
            
            // Keterangan tambahan untuk item
            $table->string('remarks')->nullable()->after('condition_complete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_borrowing_items', function (Blueprint $table) {
            $table->dropColumn([
                'brand_type',
                'condition_good',
                'condition_adequate',
                'condition_complete',
                'remarks'
            ]);
        });
    }
};
